import { route as ziggyRoute } from 'ziggy-js';

const Ziggy = {
    url: "https://ionbec.com",
    port: null,
    defaults: {},
    routes: {
        "attachment.stream": {"uri":"attachment\/stream\/{attachment_uuid}","methods":["GET","HEAD"]},
        "exam.login": {"uri":"exam","methods":["POST"]},
        "exam.logout": {"uri":"exam\/logout","methods":["GET","HEAD"]},
        "taker.register": {"uri":"taker-register","methods":["GET","HEAD"]},
        "taker.sign-up": {"uri":"taker-register","methods":["POST"]},
        "taker.register.delivery": {"uri":"taker-register\/delivery","methods":["POST"]},
        "taker.register.groups": {"uri":"taker-register\/groups","methods":["POST"]},
        "taker.login": {"uri":"taker-login","methods":["GET","HEAD"]},
        "taker.sign-in": {"uri":"taker-login","methods":["POST"]},
        "exam.finished": {"uri":"exam\/finished","methods":["GET","HEAD"]},
        "back-office.dashboard": {"uri":"back-office\/dashboard","methods":["GET","HEAD"]},
        "back-office.delivery.index": {"uri":"back-office\/delivery","methods":["GET","HEAD"]},
        "back-office.delivery.store": {"uri":"back-office\/delivery","methods":["POST"]},
        "back-office.group.index": {"uri":"back-office\/group","methods":["GET","HEAD"]},
        "back-office.group.store": {"uri":"back-office\/group","methods":["POST"]},
        "back-office.test.index": {"uri":"back-office\/test","methods":["GET","HEAD"]},
        "back-office.test.store": {"uri":"back-office\/test","methods":["POST"]},
        "back-office.category.index": {"uri":"back-office\/category","methods":["GET","HEAD"]},
        "back-office.category.store": {"uri":"back-office\/category","methods":["POST"]},
        "back-office.question-set.index": {"uri":"back-office\/question-set","methods":["GET","HEAD"]},
        "back-office.question-set.store": {"uri":"back-office\/question-set","methods":["POST"]},
        "back-office.test-taker.index": {"uri":"back-office\/test-taker","methods":["GET","HEAD"]},
        "back-office.test-taker.store": {"uri":"back-office\/test-taker","methods":["POST"]},
        "back-office.scoring.index": {"uri":"back-office\/scoring","methods":["GET","HEAD"]},
        "back-office.result.index": {"uri":"back-office\/result","methods":["GET","HEAD"]},
        "back-office.user.index": {"uri":"back-office\/user","methods":["GET","HEAD"]},
        "back-office.user.store": {"uri":"back-office\/user","methods":["POST"]},
        "back-office.profile.index": {"uri":"back-office\/profile","methods":["GET","HEAD"]},
        "back-office.clients.index": {"uri":"back-office\/clients","methods":["GET","HEAD"]},
        "back-office.clients.store": {"uri":"back-office\/clients","methods":["POST"]},
        "back-office.root-dashboard": {"uri":"back-office\/root-dashboard","methods":["GET","HEAD"]},
        "back-office.users.index": {"uri":"back-office\/users","methods":["GET","HEAD"]},
        "back-office.users.store": {"uri":"back-office\/users","methods":["POST"]},
        "exam.main": {"uri":"exam","methods":["GET","HEAD"]},
        "exam.get-taker-answer": {"uri":"exam\/questions\/{item_hash}","methods":["GET","HEAD"]},
        "exam.answer": {"uri":"exam\/answer","methods":["POST"]},
        "exam.timer.sync": {"uri":"exam\/timer\/sync","methods":["GET","HEAD"]},
        "exam.waiting-room": {"uri":"exam\/waiting-room","methods":["GET","HEAD"]},
        "taker.dashboard": {"uri":"taker\/dashboard","methods":["GET","HEAD"]},
        "taker.dashboard.card-data": {"uri":"taker\/dashboard\/data","methods":["GET","HEAD"]},
        "taker.schedules.index": {"uri":"taker\/schedules","methods":["GET","HEAD"]},
        "taker.schedules.login": {"uri":"taker\/schedules","methods":["POST"]},
        "taker.schedules.pdf": {"uri":"taker\/schedules\/{delivery_hash}\/pdf","methods":["GET","HEAD"]},
        "taker.profile.index": {"uri":"taker\/profile","methods":["GET","HEAD"]},
        "taker.profile.update-biodata": {"uri":"taker\/profile","methods":["POST"]},
        "taker.profile.update-password": {"uri":"taker\/profile\/password","methods":["POST"]},
        "api.ziggy": {"uri":"api\/ziggy","methods":["GET","HEAD"]},
        "login": {"uri":"login","methods":["GET","HEAD"]},
        "logout": {"uri":"logout","methods":["POST"]},
        "password.request": {"uri":"forgot-password","methods":["GET","HEAD"]},
        "password.reset": {"uri":"reset-password\/{token}","methods":["GET","HEAD"]},
        "password.email": {"uri":"forgot-password","methods":["POST"]},
        "password.update": {"uri":"reset-password\/{token}","methods":["POST"]},
        "register": {"uri":"register","methods":["GET","HEAD"]},
        "user-profile-information.update": {"uri":"user\/profile-information","methods":["PUT"]},
        "user-password.update": {"uri":"user\/password","methods":["PUT"]},
        "password.confirmation": {"uri":"user\/confirmed-password-status","methods":["GET","HEAD"]},
        "password.confirm": {"uri":"user\/confirm-password","methods":["POST"]},
        "two-factor.login": {"uri":"two-factor-challenge","methods":["GET","HEAD"]},
        "two-factor.enable": {"uri":"user\/two-factor-authentication","methods":["POST"]},
        "two-factor.confirm": {"uri":"user\/confirmed-two-factor-authentication","methods":["POST"]},
        "two-factor.disable": {"uri":"user\/two-factor-authentication","methods":["DELETE"]},
        "two-factor.qr-code": {"uri":"user\/two-factor-qr-code","methods":["GET","HEAD"]},
        "two-factor.secret-key": {"uri":"user\/two-factor-secret-key","methods":["GET","HEAD"]},
        "two-factor.recovery-codes": {"uri":"user\/two-factor-recovery-codes","methods":["GET","HEAD"]},
        "profile.show": {"uri":"user\/profile","methods":["GET","HEAD"]},
        "current-user-photo.destroy": {"uri":"user\/profile-photo","methods":["DELETE"]},
        "current-user.destroy": {"uri":"user","methods":["DELETE"]},
        "api.questions.attempts": {"uri":"api\/questions\/{questionHash}\/attempts","methods":["GET","HEAD"]}
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
