import Express from "express";
import fetch from "node-fetch"

const port = 3000
const app = Express()

// Accept JSON bodies
app.use(Express.json())
app.use(Express.urlencoded({ extended: true }));

// Serve files in /public
app.use(Express.static('public'))

app.get('/sign-in', (_, res) => {
  res.sendFile(`${process.cwd()}/public/sign-in.html`)
})

app.get('/sign-up', (_, res) => {
  res.sendFile(`${process.cwd()}/public/sign-up.html`)
})

app.get('/home', (_, res) => {
  res.sendFile(`${process.cwd()}/public/home.html`)
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

  if (!response.ok) {
    console.log(response);
    res.send("Something went wrong")
    return;
  }

  const r = response.json()
  if (typeof r.sessionTokenCreated === "string") {
    console.log(`Success. Session token: ${r.sessionTokenCreated}`);
    res.redirect(`/home?session-id=${sessionTokenCreated}`);
    return;
  } else {
    res.send(`Failure. ${JSON.stringify(r)}`);
  }
});

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
    // const { errors, errorMessage } = r;
    console.log("Errors: ", r);
    res.json(r);
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

