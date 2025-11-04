import { route as ziggyRoute } from 'ziggy-js';

const Ziggy = {
    url: "http://localhost:8000",
    port: 8000,
    defaults: {},
    routes: {
        "attachment.stream": {"uri":"attachment\/stream\/{attachment_uuid}","methods":["GET","HEAD"]},
        "exam.login": {"uri":"exam","methods":["POST"]},
        "exam.logout": {"uri":"exam\/logout","methods":["GET","HEAD"]},
        "taker.register": {"uri":"taker-register","methods":["GET","HEAD"]},
        "taker.sign-up": {"uri":"taker-register","methods":["POST"]},
        "exam.main": {"uri":"exam","methods":["GET","HEAD"]},
        "login": {"uri":"login","methods":["GET","HEAD"]},
        "logout": {"uri":"logout","methods":["POST"]},
        "register": {"uri":"register","methods":["GET","HEAD"]}
    }
};

if (typeof window !== 'undefined' && typeof window.Ziggy !== 'undefined') {
    Object.assign(Ziggy.routes, window.Ziggy.routes);
}

const route = (name, params = undefined, absolute = false) => {
  // Handle undefined route names gracefully
  if (!name) {
    console.warn('Route name is undefined');
    return '#';
  }

  // Force absolute to false for relative URLs
  absolute = false;

  try {
    const url = ziggyRoute(name, params, absolute, Ziggy);

    // Convert any absolute URLs to relative URLs
    if (url && typeof url === 'string') {
      // Remove protocol and domain
      return url.replace(/^https?:\/\/[^\/]+/, '');
    }

    return url || '#';
  } catch (error) {
    console.warn('Route generation failed for:', name, error.message);
    return '#';
  }
};

export { route, Ziggy };
