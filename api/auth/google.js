export async function onRequestPost(context) {
  const { request } = context;

  try {
    const { token } = await request.json();

    const googleRes = await fetch(
      `https://oauth2.googleapis.com/tokeninfo?id_token=${token}`
    );
    const payload = await googleRes.json();

    if (!payload || payload.error || !payload.sub) {
      return new Response(JSON.stringify({ error: "Google verification failed" }), {
        status: 401,
        headers: { 'Content-Type': 'application/json' }
      });
    }

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
    return new Response(JSON.stringify({
      error: "Server Error",
      details: err.message
    }), { status: 500 });
  }
}
