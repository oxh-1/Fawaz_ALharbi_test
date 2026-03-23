export async function onRequestPost(context) {
  const { request } = context;
  const { token } = await request.json();

  // 1. Verify the token with Google's API
  const googleRes = await fetch(`https://oauth2.googleapis.com/tokeninfo?id_token=${token}`);
  const payload = await googleRes.json();

  // 2. Security Check: If Google says the token is fake or expired
  if (!payload.sub) {
    return new Response(JSON.stringify({ error: "Invalid Token" }), { 
      status: 401,
      headers: { 'Content-Type': 'application/json' }
    });
  }

  // 3. Success! Prepare the user data
  const user = {
    id: payload.sub,
    email: payload.email,
    name: payload.name,
    picture: payload.picture
  };

  // 4. Return the user info to your Vue app
  return new Response(JSON.stringify({ user }), {
    headers: { 'Content-Type': 'application/json' }
  });
}
