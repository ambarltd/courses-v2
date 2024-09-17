import Express from "express";
import fetch from "node-fetch"
import { engine } from 'express-handlebars';
import { dirname } from 'path';
import { fileURLToPath } from 'url';

const port = 8080
const app = Express()

// Accept JSON bodies
app.use(Express.json())
app.use(Express.urlencoded({ extended: true }));

// Setup templating
const __dirname = dirname(fileURLToPath(import.meta.url));
app.engine('handlebars', engine());
app.set('view engine', 'handlebars');
app.set('views', `${__dirname}/views`);

app.get('/sign-in', (_, res) => {
  res.render(`sign-in`, { layout: false })
})

app.get('/sign-up', (_, res) => {
  res.render(`sign-up`, { layout: false })
})

app.get('/home', (_, res) => {
  res.render(`home`, { layout: false })
})

app.get('/', (_, res) => {
  res.render(`index`, { layout: false })
})

app.post('/sign-in', async (req, res) => {
  const { email, password } = req.body;
  const contents = {
    withUsernameOrEmail: email,
    withPassword: password,
    byDeviceLabel: "desktop",
    metadata: {
      environment: "browser",
      devicePlatform: "unknown",
      deviceModel: "unknown",
      deviceOSVersion: "unknown",
      deviceOrientation: "unknown"
    }
  }
  const response = await fetch("https://a34f7853b5c9b-pro-mon-app-app-323035764089.europe-west2.run.app/api/v1/security/session/sign-in", {
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
});

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

app.post('/sign-up', async (req, res) => {
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

  const response = await fetch("https://a34f7853b5c9b-pro-mon-app-app-323035764089.europe-west2.run.app/api/v1/identity/user/sign-up", {
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
    res.send(`Success. User id: ${r.userId}`);
    res.redirect(`/sign-in`);
    return;
  }

  res.send(`Unexpected response. ${JSON.stringify(r)}`);
});

app.listen(port, () => {
  console.log(`Example app listening on port ${port}`)
})

