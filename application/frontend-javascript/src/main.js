import Express from "express";
import fetch from "node-fetch"
import { engine } from 'express-handlebars';
import { helpers } from './handlebarsHelpers.js';
import * as path from 'path';
import { fileURLToPath } from 'url';
import Session from "express-session";
import yaml from 'js-yaml';
import { readFileSync } from 'fs';
import { dirname } from 'path';

const port = 8080
const app = Express()

const domains = {
  identity: process.env.DOMAIN_IDENTITY + "/api/v1/identity",
  security: process.env.DOMAIN_SECURITY + "/api/v1/security",
  card: process.env.DOMAIN_CREDIT_CARD_PRODUCT + "/api/v1/credit_card_product",
  enrollment: process.env.DOMAIN_CARD_ENROLLMENT + "/api/v1/credit_card_enrollment"
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
  "request-card-enrollment": domains.enrollment + "/enrollment",
  "list-user-enrollments": domains.enrollment + "/enrollment/list-enrollments"
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
  return res.redirect("/");
}

function authenticatedOrNot(req, res, next) {
  return next();
}

function authenticate(req, { token, userId, email, requestedEmail, verified }) {
  req.session.token = token;
  req.session.email = email;
  req.session.requestedEmail = requestedEmail;
  req.session.userId = userId;
  req.session.verified = verified;
}

function unauthenticate(req) {
  delete req.session.token;
}

const layouts = {
  explore: "explore",
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
app.engine('handlebars', engine({helpers}));
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
app.get('/verify-email', authenticatedOrNot, routeVerifyEmail);
app.post('/sign-in', unauthenticated, routeSignIn);
app.post('/sign-up', unauthenticated,  routeSignUp);
app.get('/verification-emails', routeVerificationEmails);
app.get('/card/products', authenticated, routeCardProducts);
app.post('/card/enrollment', authenticated, routeRequestedEnrollment);
app.get('/user/enrollments', authenticated, routeUserEnrollments);

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
  console.log(primaryEmailStatus);
  const { email, requestedEmail, verified } =
    ("unverifiedEmail" in primaryEmailStatus)
    ? { email: primaryEmailStatus.unverifiedEmail.email, requestedEmail: null, verified: false }
    : ("verifiedEmail" in primaryEmailStatus)
    ? { email: primaryEmailStatus.verifiedEmail.email, requestedEmail: null, verified: true }
    : ("verifiedButRequestedNewEmail" in primaryEmailStatus)
    ? { email: primaryEmailStatus.verifiedButRequestedNewEmail.verifiedEmail, requestedEmail: primaryEmailStatus.verifiedButRequestedNewEmail.requestedEmail, verified: false }
    : new Error(`Unknown state of primaryEmailStatus. ${JSON.stringify(primaryEmailStatus)}`);

  return { userId, email, requestedEmail, verified };
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
      requestedEmail: req.session.requestedEmail,
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
    const { email, userId, verified, requestedEmail } = await userDetails(token);
    authenticate(req, { token, email, userId, verified, requestedEmail });
  }
  return renderUserDetails(req, res, { successMessage: "Details changed successfully" });
}

async function routeVerifyEmail(req,res) {
  const verificationCode = req.query.code;
  const contents = { verificationCode, metadata };

  if (req.session.token) {
      unauthenticate(req);
      await fetch(endpoints["sign-out"], {
          method: "POST",
          body: JSON.stringify(contents, null, 2),
          headers: {
            'Content-Type': 'application/json',
            'X-With-Session-Token': req.session.token
          }
      });
  }


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
      ? "Invalid email or password. Or unverified email."
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
    const { email, userId, verified, requestedEmail } = await userDetails(token);
    authenticate(req, { token, email, userId, requestedEmail, verified });
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
  try {
    const response = await fetch(endpoints["list-sent-verification-emails"], {
      method: "POST",
      body: '{}',
      headers: { 'Content-Type': 'application/json' }
    });

    if (!response.ok) {
      const error = getError(await response.json());
      errorPage(res, error);
      return;
    }

    const emails = await response.json();

    res.render("verification-emails", {
      layout: layouts.signedOut,
      locals: {
        title: "Verification Emails",
        emails: emails,
        response: JSON.stringify(response.json),
        json: JSON.stringify(response.json)
      }
    });
  } catch (error) {
    console.error("Error fetching verification emails:", error);
    errorPage(res, "Failed to fetch verification emails.");
  }
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

async function routeRequestedEnrollment(req, res) {

  console.log('Request body: ' + req.body)

  const contents = {
    productId: req.body.productId,
    annualIncome: req.body.annualIncome
  };

  const response = await fetch(endpoints["request-card-enrollment"], {
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

  return res.render("card/enrollments", {
    layout: layouts.main,
    locals: {
      title: "Card Enrollment Requests",
      enrollments: r
    }
  });
}

async function routeUserEnrollments(req, res) {
  const contents = {};

  const response = await fetch(endpoints["list-user-enrollments"], {
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

  return res.render("card/enrollments", {
    layout: layouts.main,
    locals: {
      title: "Card Enrollment Requests",
      enrollments: r
    }
  });
}

app.get('/event-bus-yml', (req, res) => {
  try {
    const fileContents = readFileSync('/ambar-yml/ambar-config.yaml', 'utf8');
    const data = yaml.load(fileContents);
    res.json(data);
  } catch (error) {
    res.status(500).json({ error: 'Error reading YAML file' });
  }
});
app.get('/event-bus.yml', (req, res) => {
    try {
        const fileContents = readFileSync('/ambar-yml/ambar-config.yaml', 'utf8');
        res.type('text/plain').send(fileContents);
    } catch (error) {
        res.status(500).send('Error reading YAML file');
    }
});

app.get('/event-bus-with-ambar-iframe', (req, res) => {
  res.render('explorer/event-bus-with-ambar-iframe', { layout: false });
});

app.get('/event-bus-with-ambar', (req, res) => {
  res.render('explorer/event-bus-with-ambar', { layout: layouts.explore, locals: {title: 'Explore Event Bus', activeEventBus: true}  });
});

app.get('/event-store-with-postgres', (req, res) => {
  res.render('explorer/event-store-with-postgres', { layout: layouts.explore, locals: {title: 'Explore Event Store', activeEventStore: true} });
});

app.get('/projections-with-mongo', (req, res) => {
  res.render('explorer/projections-with-mongo', { layout: layouts.explore, locals: {title: 'Explore Projections', activeProjection: true}  });
});

app.get("*", authenticated, render("404", { title: "Not Found" }))

app.listen(port, () => {
  console.log(`Server listening on port ${port}`)
})