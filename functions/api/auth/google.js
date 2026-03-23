export async function onRequestPost(context) { // Full context object
  const { request } = context; // Then destructure
  try {
    const { token } = await request.json();

    // 1. Verify with Google
    const googleRes = await fetch(`https://oauth2.googleapis.com/tokeninfo?id_token=${token}`);
    const payload = await googleRes.json();

    // 2. Check if Google rejected it
    if (!payload || payload.error || !payload.sub) {
      return new Response(JSON.stringify({ error: "Google verification failed" }), { 
        status: 401,
        headers: { 'Content-Type': 'application/json' }
      });
    }

    // 3. Success!
    const user = {
      id: payload.sub,
      email: payload.email,
      name: payload.name,
      picture: payload.picture
    };

    return new Response(JSON.stringify({ user }), {
      headers: { 'Content-Type': 'application/json' }
    });

  } catch (err) {
    return new Response(JSON.stringify({ error: "Server Error", details: err.message }), { status: 500 });
  }
}
