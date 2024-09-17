import Express from "express";
import fetch from "node-fetch"
import { engine } from 'express-handlebars';
import { dirname } from 'path';
import { fileURLToPath } from 'url';

const port = 8080
const app = Express()

const domains = {
  identity: process.env.DOMAIN_IDENTITY + "/api/v1/identity",
  security: process.env.DOMAIN_SECURITY + "/api/v1/security"
}

const endpoints = {
  "request-primary-email-change": domains.identity + "/user/request-primary-email-change",
  "sign-up": domains.identity + "/user/sign-up",
  "verify-primary-email": domains.identity + "/user/verify-primary-email",
  "refresh-token": domains.security + "/session/refresh-token",
  "sign-in": domains.security + "/session/sign-in",
  "sign-out": domains.security + "session/sign-out"
}

// Accept JSON bodies
app.use(Express.json())
app.use(Express.urlencoded({ extended: true }));

// Setup templating
const __dirname = dirname(fileURLToPath(import.meta.url));
app.engine('handlebars', engine());
app.set('view engine', 'handlebars');
app.set('views', `${__dirname}/views`);

app.get('/sign-in', (_, res) => { res.render(`sign-in`, { layout: false }) })
app.get('/sign-up', (_, res) => { res.render(`sign-up`, { layout: false }) })
app.get('/sign-up-success', (_, res) => { res.render(`sign-up-success`, { layout: false }) })
app.get('/home', (_, res) => { res.render(`home`, { layout: false }) })
app.get('/', (_, res) => { res.render(`home`, { layout: false }) })
app.get('/verify-email', routeVerifyEmail);
app.post('/sign-in', routeSignIn);
app.post('/sign-up',  routeSignUp);

// Default metadata for requests.
const metadata = {
  environment: "browser",
  devicePlatform: "unknown",
  deviceModel: "unknown",
  deviceOSVersion: "unknown",
  deviceOrientation: "unknown"
};

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
      layout: false,
      error,
    });
    return;
  }

  res.render(`verify-email`, { layout: false })
}

async function routeSignIn(req, res) {
  const { email, password } = req.body;
  const contents = {
    withUsernameOrEmail: email,
    withPassword: password,
    byDeviceLabel: "desktop",
  }
  const response = await fetch(endpoints["sign-in"], {
      method: "POST",
      body: JSON.stringify(contents, null, 2),
      headers: {'Content-Type': 'application/json'}
  });

  console.log(endpoints["sign-in"])
  const r = await response.json();
  if (!response.ok) {
    const rawError = getError(r);
    const error =
      rawError == "Security_Session_SignIn_UserNotFound"
      ? "Invalid email or password"
      : rawError

    res.render("sign-in", {
      layout: false,
      error,
    });
    return;
  }

  if (typeof r.sessionTokenCreated === "string") {
    console.log(`Success. Session token: ${r.sessionTokenCreated}`);
    res.redirect(`/home?session-id=${sessionTokenCreated}`);
    return;
  } else {
    res.send(`Failure. ${JSON.stringify(r)}`);
  }
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
    metadata: {
      environment: "browser",
      devicePlatform: "unknown",
      deviceModel: "unknown",
      deviceOSVersion: "unknown",
      deviceOrientation: "unknown"
    }
  }

  console.log(JSON.stringify(contents, null, 2));

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
      layout: false,
      error,
      username,
      email,
    });
    return;
  }

  if (typeof r.userId === "string") {
    res.redirect(`/sign-up-success`);
    return;
  }

  res.send(`Unexpected response. ${JSON.stringify(r)}`);
}

app.listen(port, () => {
  console.log(`Example app listening on port ${port}`)
})

