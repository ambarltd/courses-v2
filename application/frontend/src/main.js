import Express from "express";
import fetch from "node-fetch"
import { engine } from 'express-handlebars';
import * as path from 'path';
import { fileURLToPath } from 'url';
import Session from "express-session";

const port = 8080
const app = Express()

const domains = {
  identity: process.env.DOMAIN_IDENTITY + "/api/v1/identity",
  security: process.env.DOMAIN_SECURITY + "/api/v1/security",
  card: process.env.DOMAIN_CREDIT_CARD_PRODUCT + "/api/v1/credit_card_product"
}

const endpoints = {
  "request-primary-email-change": domains.identity + "/user/request-primary-email-change",
  "sign-up": domains.identity + "/user/sign-up",
  "user-details": domains.identity + "/user/details",
  "verify-primary-email": domains.identity + "/user/verify-primary-email",
  "list-sent-verification-emails": domains.identity + "/user/list-sent-verification-emails",
  "refresh-token": domains.security + "/session/refresh-token",
  "sign-in": domains.security + "/session/sign-in",
  "sign-out": domains.security + "/session/sign-out",
  "list-credit-card-products": domains.card + "/product/list-items",
  "activate-product": domains.card + "/product/activate",
  "deactivate-product": domains.card + "/product/deactivate"
}

// Accept JSON bodies
app.use(Express.json())
app.use(Express.urlencoded({ extended: true }));

// Handle sessions
app.use(Session({
  secret: 'keyboard cat',
  resave: false,
  saveUninitialized: true
}))

// Middleware to ensure authentication.
function authenticated(req, res, next) {
  if (req.session.token) {
    return next();
  }
  return res.redirect("/sign-in");
}

// Middleware to ensure user is not authenticated.
function unauthenticated(req, res, next) {
  if (!req.session.token) {
    return next();
  }
  return res.redirect("/home");
}

function authenticate(req, { token, userId, email, verified }) {
  req.session.token = token;
  req.session.email = email;
  req.session.userId = userId;
  req.session.verified = verified;
}

function unauthenticate(req) {
  delete req.session.token;
}

const layouts = {
  signedOut: "signed-out",
  main: "main"
}

function render(template, locals) {
  return function (_, res) {
    return res.render(template, { layout: layouts.main, locals });
  }
}

function renderSignedOut(template, locals) {
  return function (_, res) {
    return res.render(template, { layout: layouts.signedOut, locals });
  }
}

// Setup templating
const __dirname = path.dirname(fileURLToPath(import.meta.url));
app.engine('handlebars', engine());
app.set('view engine', 'handlebars');
app.set('views', path.join(__dirname, "views"));

// Serve static files
const static_dir = path.join(path.dirname(__dirname), 'assets');
app.use("/assets", Express.static(static_dir))

app.get('/sign-in', unauthenticated, renderSignedOut("sign-in", { title: "Sign in" }))
app.get('/sign-up', unauthenticated, renderSignedOut("sign-up", { title: "Sign-up" }))
app.get('/sign-up-success', unauthenticated, renderSignedOut("sign-up-success", { title: "Sign-up success" }))
app.get('/user/details', authenticated, routeUserDetails)
app.post('/user/details', authenticated, routeUserDetailsPost)
app.get('/logout', authenticated, routeLogout)
app.get('/', authenticated, render("home", { title: "Home" }))
app.get('/verify-email', unauthenticated, routeVerifyEmail);
app.post('/sign-in', unauthenticated, routeSignIn);
app.post('/sign-up', unauthenticated,  routeSignUp);
app.get('/verification-emails', routeVerificationEmails);
app.get('/card/products', authenticated, routeCardProducts);
app.post('/card/products', authenticated,  cardToggle);

async function userDetails(token) {
  const response = await fetch(endpoints["user-details"], {
      method: "POST",
      body: '{}',
      headers: {
        'Content-Type': 'application/json',
        'X-With-Session-Token': token
      }
  });

  const r = await response.json();
  if (!response.ok) {
    const error = getError(r);
    throw new Error(error);
  }

  const { userId, primaryEmailStatus } = r;
  const { email, verified } =
    ("unverifiedEmail" in primaryEmailStatus)
    ? { email: primaryEmailStatus.unverifiedEmail.email, verified: false }
    : ("verifiedEmail" in primaryEmailStatus)
    ? { email: primaryEmailStatus.verifiedEmail.email, verified: true }
    : ("verifiedButRequestedNewEmail" in primaryEmailStatus)
    ? { email: primaryEmailStatus.verifiedButRequestedNewEmail.requestedEmail, verified: false }
    : new Error(`Unknown state of primaryEmailStatus. ${JSON.stringify(primaryEmailStatus)}`);

  return { userId, email, verified };
}


// Default metadata for requests.
const metadata = {
  environment: "browser",
  devicePlatform: "unknown",
  deviceModel: "unknown",
  deviceOSVersion: "unknown",
  deviceOrientation: "unknown"
};

function renderUserDetails(req, res, { successMessage, failureMessage }) {
  res.render("details", {
    layout: layouts.main,
    locals: {
      title: "User details",
      email: req.session.email,
      userId: req.session.userId,
      verified: req.session.verified,
      successMessage,
      failureMessage
    }
  });
}
function routeUserDetails(req, res) {
  return renderUserDetails(req, res, {});
}

async function routeUserDetailsPost(req, res) {
  const token = req.session.token;
  const { password, newEmailRequested } = req.body;
  const contents = { metadata, password, newEmailRequested };

  const response = await fetch(endpoints["request-primary-email-change"], {
      method: "POST",
      body: JSON.stringify(contents, null, 2),
      headers: {
        'Content-Type': 'application/json',
        'X-With-Session-Token': token
      }
  });

  const r = await response.json()

  if (!response.ok) {
    const error = getError(r);
    return renderUserDetails(req, res, { failureMessage: error });
  }


  { // update user details.
    const { email, userId, verified } = await userDetails(token);
    authenticate(req, { token, email, userId, verified });
  }
  return renderUserDetails(req, res, { successMessage: "Details changed successfully" });
}

async function routeVerifyEmail(req,res) {
  const verificationCode = req.query.code;
  const contents = { verificationCode, metadata };
  const response = await fetch(endpoints["verify-primary-email"], {
      method: "POST",
      body: JSON.stringify(contents, null, 2),
      headers: {'Content-Type': 'application/json'}
  });
  const r = await response.json();

  if (!response.ok) {
    const error = getError(r);
    res.render("verify-email", {
      layout: layouts.signedOut,
      locals: { title: "Verify email", error },
    });
    return;
  }

  res.render(`verify-email`, {
    layout: layouts.signedOut,
    locals: { title: "Verify email" },
  })
}

async function routeSignIn(req, res) {
  const { email, password } = req.body;

  const contents = {
    withUsernameOrEmail: email,
    withPassword: password,
    byDeviceLabel: "desktop",
    metadata
  }
  const response = await fetch(endpoints["sign-in"], {
      method: "POST",
      body: JSON.stringify(contents, null, 2),
      headers: {'Content-Type': 'application/json'}
  });

  const r = await response.json();
  if (!response.ok) {
    const rawError = getError(r);
    const error =
      rawError == "Security_Session_SignIn_UserNotFound"
      ? "Invalid email or password"
      : rawError

    res.render("sign-in", {
      layout: layouts.signedOut,
      locals: { title: "Sign in", error },
    });
    return;
  }

  const token = r.sessionTokenCreated;
  if (typeof token !== "string") {
    res.send(`Failure. ${JSON.stringify(r)}`);
    return;
  }

  try {
    const { email, userId, verified } = await userDetails(token);
    authenticate(req, { token, email, userId, verified });
  } catch (e) {
    errorPage(res, e);
  }
  res.redirect("/");
};

// Parse error from a failure response
function getError({ errors, errorIdentifier, errorMessage }) {
  return (errors.length > 0 && errorMessage.length > 0)
    ? `${errorMessage}: ${errors.join(". ")}`
    : errorMessage.length > 0
    ? errorMessage
    : errors.length > 0
    ? errors[0]
    : errorIdentifier
}

async function routeSignUp(req, res) {
  const { email, password, username } = req.body;
  const contents = {
    primaryEmail: email,
    password: password,
    username: username,
    termsOfUseAccepted: true,
    metadata
  }

  const response = await fetch(endpoints["sign-up"], {
      method: "POST",
      body: JSON.stringify(contents, null, 2),
      headers: {'Content-Type': 'application/json'}
  });

  const r = await response.json()

  if (!response.ok) {
    const rawError = getError(r);
    const error =
      rawError == "Identity_User_SignUp_InvalidPassword"
      ? "Invalid password. A password must have between 10 and 64 characters and contain a number, an upper case letter, a lower case letter and a special character."
      : rawError;

    res.render("sign-up", {
      layout: layouts.signedOut,
      locals: { title: "Sign-up", error, username, email }
    });
    return;
  }

  if (typeof r.userId === "string") {
    res.redirect(`/sign-up-success`);
    return;
  }

  res.send(`Unexpected response. ${JSON.stringify(r)}`);
}

async function routeLogout(req, res) {
  const token = req.session.token;
  const contents = { metadata };
  unauthenticate(req);

  const response = await fetch(endpoints["sign-out"], {
      method: "POST",
      body: JSON.stringify(contents, null, 2),
      headers: {
        'Content-Type': 'application/json',
        'X-With-Session-Token': token
      }
  });

  const r = await response.json()

  if (!response.ok) {
    const error = getError(r);
    errorPage(res, error);
    return;
  }

  res.redirect("/sign-in");
}

function errorPage(res, error) {
  res.send(error);
}

async function routeVerificationEmails(req, res) {

  const response = await fetch(endpoints["list-sent-verification-emails"], {
      method: "POST",
      body: '{}',
      headers: {
        'Content-Type': 'application/json',
      }
  });
  if (!response.ok) {
    console.log(response)
    const error = getError(r);
    errorPage(res, error);
    return;
  }

  const r = await response.json()
  res.json(r);
}
async function routeCardProducts(req, res) {
  const contents = {};

  const response = await fetch(endpoints["list-credit-card-products"], {
      method: "POST",
      body: JSON.stringify(contents, null, 2),
      headers: {
        'Content-Type': 'application/json',
        'X-With-Session-Token': req.session.token
      }
  });
  const r = await response.json()
  if (!response.ok) {
    const error = getError(r);
    errorPage(res, error);
    return;
  }

  return res.render("card/products", {
    layout: layouts.main,
    locals: {
      title: "Card Products",
      products: r
    }
  });
}

async function cardToggle(req, res) {
  const productId = req.body.productId;
  const active = req.body.active;

  if (!productId) {
    return res.status(400).json({ error: 'Product ID is required' });
  }

  try {
    // Make the appropriate fetch request based on the product's current status
    const endpoint = active === "true"
        ? endpoints["deactivate-product"] + "/" + productId
        : endpoints["activate-product"] + "/" + productId;

    const response = await fetch(endpoint, {
      method: "POST",
      body: '{}',
      headers: {
        'Content-Type': 'application/json',
      }
    });

    // Bit of a hack, the request -> event -> projection will take some time.
    // Realistically you would update the interface locally, and refresh state async
    await sleep(2000);

    // After successfully toggling the product status, render the updated product list
    return await routeCardProducts(req, res);
  } catch (error) {
    console.error('Error in cardToggle:', error);
    return await routeCardProducts(req, res);
  }
}

function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

app.get("*", authenticated, render("404", { title: "Not Found" }))

app.listen(port, () => {
  console.log(`Server listening on port ${port}`)
})

