import { route as ziggyRoute } from 'ziggy-js';

const Ziggy = {
    url: "https://ionbec.com",
    port: null,
    defaults: {},
    routes: {
        // Attachment routes
        "attachment.stream": {"uri":"attachment\/stream\/{attachment_uuid}","methods":["GET","HEAD"]},

        // Exam routes
        "exam.login": {"uri":"exam","methods":["POST"]},
        "exam.logout": {"uri":"exam\/logout","methods":["GET","HEAD"]},
        "exam.finished": {"uri":"exam\/finished","methods":["GET","HEAD"]},
        "exam.main": {"uri":"exam","methods":["GET","HEAD"]},
        "exam.get-taker-answer": {"uri":"exam\/questions\/{item_hash}","methods":["GET","HEAD"]},
        "exam.answer": {"uri":"exam\/answer","methods":["POST"]},
        "exam.timer.sync": {"uri":"exam\/timer\/sync","methods":["GET","HEAD"]},
        "exam.waiting-room": {"uri":"exam\/waiting-room","methods":["GET","HEAD"]},
        "exam.waiting-room.status": {"uri":"exam\/waiting-room\/status","methods":["GET","HEAD"]},

        // Taker registration routes
        "taker.register": {"uri":"taker-register","methods":["GET","HEAD"]},
        "taker.sign-up": {"uri":"taker-register","methods":["POST"]},
        "taker.register.delivery": {"uri":"taker-register\/delivery","methods":["POST"]},
        "taker.register.groups": {"uri":"taker-register\/groups","methods":["POST"]},
        "taker.login": {"uri":"taker-login","methods":["GET","HEAD"]},
        "taker.sign-in": {"uri":"taker-login","methods":["POST"]},

        // Taker dashboard routes
        "taker.dashboard": {"uri":"taker\/dashboard","methods":["GET","HEAD"]},
        "taker.dashboard.card-data": {"uri":"taker\/dashboard\/data","methods":["GET","HEAD"]},
        "taker.schedules.index": {"uri":"taker\/schedules","methods":["GET","HEAD"]},
        "taker.schedules.login": {"uri":"taker\/schedules","methods":["POST"]},
        "taker.schedules.pdf": {"uri":"taker\/schedules\/{delivery_hash}\/pdf","methods":["GET","HEAD"]},
        "taker.profile.index": {"uri":"taker\/profile","methods":["GET","HEAD"]},
        "taker.profile.update-biodata": {"uri":"taker\/profile","methods":["POST"]},
        "taker.profile.update-password": {"uri":"taker\/profile\/password","methods":["POST"]},

        // Back-office live interview routes
        "back-office.interview.live-question": {"uri":"back-office\/live-interview\/{delivery_hash}","methods":["GET","HEAD"]},
        "back-office.live-interview.show-question": {"uri":"back-office\/live-interview\/{delivery_hash}\/attempt\/{attempt_hash}","methods":["POST"]},

        // Back-office main dashboard
        "back-office.dashboard": {"uri":"back-office\/dashboard","methods":["GET","HEAD"]},
        "back-office.root-dashboard": {"uri":"back-office\/root-dashboard","methods":["GET","HEAD"]},

        // Back-office delivery routes
        "back-office.delivery.index": {"uri":"back-office\/delivery","methods":["GET","HEAD"]},
        "back-office.delivery.store": {"uri":"back-office\/delivery","methods":["POST"]},
        "back-office.delivery.scoring": {"uri":"back-office\/delivery\/{delivery_hash}\/scoring","methods":["GET","HEAD"]},
        "back-office.delivery.taker": {"uri":"back-office\/delivery\/{delivery_hash}\/taker","methods":["GET","HEAD"]},
        "back-office.delivery.question": {"uri":"back-office\/delivery\/{delivery_hash}\/question","methods":["GET","HEAD"]},
        "back-office.delivery.update": {"uri":"back-office\/delivery\/{delivery_hash}","methods":["PUT"]},
        "back-office.delivery.destroy": {"uri":"back-office\/delivery\/{delivery_hash}","methods":["DELETE"]},
        "back-office.delivery.generate-token": {"uri":"back-office\/delivery\/{delivery_hash}\/generate-token","methods":["POST"]},
        "back-office.delivery.taker-pdf": {"uri":"back-office\/delivery\/{delivery_hash}\/taker-pdf","methods":["GET","HEAD"]},
        "back-office.delivery.scoring-pdf": {"uri":"back-office\/delivery\/{delivery_hash}\/scoring\/{attempt_hash}\/pdf","methods":["GET","HEAD"]},
        "back-office.delivery.scoring-detail": {"uri":"back-office\/delivery\/{delivery_hash}\/scoring\/{attempt_hash}\/detail","methods":["GET","HEAD"]},
        "back-office.delivery.scoring-submit": {"uri":"back-office\/delivery\/scoring","methods":["POST"]},
        "back-office.delivery.question-scoring-submit": {"uri":"back-office\/delivery\/scoring-question","methods":["POST"]},
        "back-office.delivery.finish-scoring": {"uri":"back-office\/delivery\/finish-scoring","methods":["POST"]},
        "back-office.delivery.finish": {"uri":"back-office\/delivery\/{delivery_hash}\/finish","methods":["POST"]},
        "back-office.delivery.goto": {"uri":"back-office\/delivery\/{delivery_hash}\/goto","methods":["GET","HEAD"]},
        "back-office.delivery.preview": {"uri":"back-office\/delivery\/{delivery_hash}\/preview","methods":["GET","HEAD"]},
        "back-office.delivery.attempt-interview": {"uri":"back-office\/delivery\/{delivery_hash}\/takers\/{taker_hash}\/interview","methods":["POST"]},
        "back-office.delivery.clear-login": {"uri":"back-office\/delivery\/{delivery_hash}\/takers\/{taker_hash}\/clear-login","methods":["POST"]},

        // Back-office test routes
        "back-office.test.index": {"uri":"back-office\/test","methods":["GET","HEAD"]},
        "back-office.test.store": {"uri":"back-office\/test","methods":["POST"]},
        "back-office.test.detail": {"uri":"back-office\/test\/{exam_hash}","methods":["GET","HEAD"]},
        "back-office.test.update": {"uri":"back-office\/test\/{exam_hash}","methods":["PUT"]},
        "back-office.test.destroy": {"uri":"back-office\/text\/{exam_hash}","methods":["DELETE"]},
        "back-office.test.add-question-set": {"uri":"back-office\/test\/{exam_hash}\/add-question-set","methods":["POST"]},
        "back-office.test.remove-question-set": {"uri":"back-office\/test\/{exam_hash}\/remove-question-set","methods":["POST"]},
        "back-office.test.pdf": {"uri":"back-office\/test\/{exam_hash}\/pdf","methods":["GET","HEAD"]},
        "back-office.test.reorder": {"uri":"back-office\/test\/{exam_hash}\/reorder","methods":["POST"]},

        // Back-office group routes
        "back-office.group.index": {"uri":"back-office\/group","methods":["GET","HEAD"]},
        "back-office.group.store": {"uri":"back-office\/group","methods":["POST"]},
        "back-office.group.taker": {"uri":"back-office\/group\/{group_hash}\/takers","methods":["GET","HEAD"]},
        "back-office.group.delivery": {"uri":"back-office\/group\/{group_hash}\/deliveries","methods":["GET","HEAD"]},
        "back-office.group.result": {"uri":"back-office\/group\/{group_hash}\/results","methods":["GET","HEAD"]},
        "back-office.group.update": {"uri":"back-office\/group\/{group_hash}","methods":["PUT"]},
        "back-office.group.destroy": {"uri":"back-office\/group\/{group_hash}","methods":["DELETE"]},
        "back-office.group.add-test-taker": {"uri":"back-office\/group\/{group_hash}\/add-test-taker","methods":["POST"]},
        "back-office.group.remove-test-taker": {"uri":"back-office\/group\/{group_hash}\/remove-test-taker","methods":["POST"]},
        "back-office.group.add-delivery": {"uri":"back-office\/group\/{group_hash}\/add-delivery","methods":["POST"]},
        "back-office.group.remove-delivery": {"uri":"back-office\/group\/{group_hash}\/remove-delivery","methods":["POST"]},
        "back-office.group.taker-pdf": {"uri":"back-office\/group\/{group_hash}\/takers\/pdf","methods":["GET","HEAD"]},
        "back-office.group.result-pdf": {"uri":"back-office\/group\/{group_hash}\/results\/pdf","methods":["GET","HEAD"]},

        // Back-office category routes
        "back-office.category.index": {"uri":"back-office\/category","methods":["GET","HEAD"]},
        "back-office.category.store": {"uri":"back-office\/category","methods":["POST"]},
        "back-office.category.update": {"uri":"back-office\/category\/{category_hash}","methods":["PUT"]},
        "back-office.category.destroy": {"uri":"back-office\/category\/{category_hash}","methods":["DELETE"]},

        // Back-office question set routes
        "back-office.question-set.index": {"uri":"back-office\/question-set","methods":["GET","HEAD"]},
        "back-office.question-set.create": {"uri":"back-office\/question-set\/create","methods":["GET","HEAD"]},
        "back-office.question-set.detail": {"uri":"back-office\/question-set\/{item_hash}","methods":["GET","HEAD"]},
        "back-office.question-set.store": {"uri":"back-office\/question-set","methods":["POST"]},
        "back-office.question-set.create-or-update": {"uri":"back-office\/question-set\/create-or-update","methods":["POST"]},
        "back-office.question-set.destroy": {"uri":"back-office\/question-set\/{item_hash}","methods":["DELETE"]},
        "back-office.question-set.attach": {"uri":"back-office\/question-set\/{item_hash}\/attach","methods":["POST"]},
        "back-office.question-set.delete-attach": {"uri":"back-office\/question-set\/{item_hash}\/delete-attach","methods":["POST"]},

        // Back-office question pack routes
        "back-office.question-pack.index": {"uri":"back-office\/question-pack","methods":["GET","HEAD"]},

        // Back-office test taker routes
        "back-office.test-taker.index": {"uri":"back-office\/test-taker","methods":["GET","HEAD"]},
        "back-office.test-taker.store": {"uri":"back-office\/test-taker","methods":["POST"]},
        "back-office.test-taker.update": {"uri":"back-office\/test-taker\/{taker_hash}","methods":["PUT"]},
        "back-office.test-taker.destroy": {"uri":"back-office\/test-taker\/{taker_hash}","methods":["DELETE"]},
        "back-office.test-taker.generate-password": {"uri":"back-office\/test-taker\/generate-password","methods":["POST"]},
        "back-office.test-taker.sent-password": {"uri":"back-office\/test-taker\/sent-password","methods":["POST"]},
        "back-office.test-taker.verification": {"uri":"back-office\/test-taker\/{taker_hash}\/verification","methods":["POST"]},

        // Back-office scoring routes
        "back-office.scoring.index": {"uri":"back-office\/scoring","methods":["GET","HEAD"]},
        "back-office.scoring.detail": {"uri":"back-office\/scoring\/{delivery_hash}","methods":["GET","HEAD"]},
        "back-office.scoring.pdf": {"uri":"back-office\/scoring\/{delivery_hash}\/pdf\/{attempt_hash}","methods":["GET","HEAD"]},
        "back-office.scoring.exam": {"uri":"back-office\/scoring\/{delivery_hash}\/exam\/{attempt_hash}","methods":["GET","HEAD"]},
        "back-office.scoring.scoring-submit": {"uri":"back-office\/scoring\/submit-score","methods":["POST"]},

        // Back-office result routes
        "back-office.result.index": {"uri":"back-office\/result","methods":["GET","HEAD"]},
        "back-office.result.detail": {"uri":"back-office\/result\/{group_hash}","methods":["GET","HEAD"]},
        "back-office.result.pdf": {"uri":"back-office\/result\/{group_hash}\/pdf","methods":["GET","HEAD"]},

        // Back-office user management routes
        "back-office.user.index": {"uri":"back-office\/user","methods":["GET","HEAD"]},
        "back-office.user.store": {"uri":"back-office\/user","methods":["POST"]},
        "back-office.user.update": {"uri":"back-office\/user\/{user_hash}","methods":["PUT"]},
        "back-office.user.destroy": {"uri":"back-office\/user\/{user_hash}","methods":["DELETE"]},
        "back-office.user.change-password": {"uri":"back-office\/user\/{user_hash}\/change-password","methods":["POST"]},

        // Back-office profile routes
        "back-office.profile.index": {"uri":"back-office\/profile","methods":["GET","HEAD"]},
        "back-office.profile.update-biodata": {"uri":"back-office\/profile","methods":["POST"]},
        "back-office.profile.update-password": {"uri":"back-office\/profile\/password","methods":["POST"]},
        "back-office.profile.update-image": {"uri":"back-office\/profile\/image","methods":["POST"]},

        // Back-office client management routes
        "back-office.clients.index": {"uri":"back-office\/clients","methods":["GET","HEAD"]},
        "back-office.clients.create": {"uri":"back-office\/clients\/create","methods":["GET","HEAD"]},
        "back-office.clients.store": {"uri":"back-office\/clients","methods":["POST"]},
        "back-office.clients.show": {"uri":"back-office\/clients\/{client}","methods":["GET","HEAD"],"bindings":{"client":"id"}},
        "back-office.clients.edit": {"uri":"back-office\/clients\/{client}\/edit","methods":["GET","HEAD"],"bindings":{"client":"id"}},
        "back-office.clients.update": {"uri":"back-office\/clients\/{client}","methods":["PUT"],"bindings":{"client":"id"}},
        "back-office.clients.destroy": {"uri":"back-office\/clients\/{client}","methods":["DELETE"],"bindings":{"client":"id"}},
        "back-office.clients.toggle-status": {"uri":"back-office\/clients\/{client}\/toggle-status","methods":["PATCH"],"bindings":{"client":"id"}},
        "back-office.clients.statistics": {"uri":"back-office\/clients\/{client}\/statistics","methods":["GET","HEAD"],"bindings":{"client":"id"}},
        "back-office.clients.clone": {"uri":"back-office\/clients\/{client}\/clone","methods":["POST"],"bindings":{"client":"id"}},
        "back-office.clients.suggest-slug": {"uri":"back-office\/clients\/suggest-slug\/{name}","methods":["GET","HEAD"]},

        // Back-office user management (root) routes
        "back-office.users.index": {"uri":"back-office\/users","methods":["GET","HEAD"]},
        "back-office.users.create": {"uri":"back-office\/users\/create","methods":["GET","HEAD"]},
        "back-office.users.store": {"uri":"back-office\/users","methods":["POST"]},
        "back-office.users.edit": {"uri":"back-office\/users\/{user}\/edit","methods":["GET","HEAD"],"bindings":{"user":"id"}},
        "back-office.users.update": {"uri":"back-office\/users\/{user}","methods":["PUT"],"bindings":{"user":"id"}},
        "back-office.users.destroy": {"uri":"back-office\/users\/{user}","methods":["DELETE"],"bindings":{"user":"id"}},

        // API routes
        "api.ziggy": {"uri":"api\/ziggy","methods":["GET","HEAD"]},
        "api.questions.attempts": {"uri":"api\/questions\/{questionHash}\/attempts","methods":["GET","HEAD"]},

        // Authentication routes
        "login": {"uri":"login","methods":["GET","HEAD"]},
        "logout": {"uri":"logout","methods":["POST"]},
        "register": {"uri":"register","methods":["GET","HEAD"]},

        // Password routes
        "password.request": {"uri":"forgot-password","methods":["GET","HEAD"]},
        "password.reset": {"uri":"reset-password\/{token}","methods":["GET","HEAD"]},
        "password.email": {"uri":"forgot-password","methods":["POST"]},
        "password.update": {"uri":"reset-password","methods":["POST"]},

        // User profile routes
        "user-profile-information.update": {"uri":"user\/profile-information","methods":["PUT"]},
        "user-password.update": {"uri":"user\/password","methods":["PUT"]},
        "password.confirmation": {"uri":"user\/confirmed-password-status","methods":["GET","HEAD"]},
        "password.confirm": {"uri":"user\/confirm-password","methods":["POST"]},
        "profile.show": {"uri":"user\/profile","methods":["GET","HEAD"]},
        "other-browser-sessions.destroy": {"uri":"user\/other-browser-sessions","methods":["DELETE"]},
        "current-user-photo.destroy": {"uri":"user\/profile-photo","methods":["DELETE"]},
        "current-user.destroy": {"uri":"user","methods":["DELETE"]},

        // Two-factor authentication routes
        "two-factor.login": {"uri":"two-factor-challenge","methods":["GET","HEAD"]},
        "two-factor.enable": {"uri":"user\/two-factor-authentication","methods":["POST"]},
        "two-factor.confirm": {"uri":"user\/confirmed-two-factor-authentication","methods":["POST"]},
        "two-factor.disable": {"uri":"user\/two-factor-authentication","methods":["DELETE"]},
        "two-factor.qr-code": {"uri":"user\/two-factor-qr-code","methods":["GET","HEAD"]},
        "two-factor.secret-key": {"uri":"user\/two-factor-secret-key","methods":["GET","HEAD"]},
        "two-factor.recovery-codes": {"uri":"user\/two-factor-recovery-codes","methods":["GET","HEAD"]},

        // Horizon monitoring routes
        "horizon.stats.index": {"uri":"horizon\/api\/stats","methods":["GET","HEAD"]},
        "horizon.workload.index": {"uri":"horizon\/api\/workload","methods":["GET","HEAD"]},
        "horizon.masters.index": {"uri":"horizon\/api\/masters","methods":["GET","HEAD"]},
        "horizon.monitoring.index": {"uri":"horizon\/api\/monitoring","methods":["GET","HEAD"]},
        "horizon.monitoring.store": {"uri":"horizon\/api\/monitoring","methods":["POST"]},
        "horizon.monitoring-tag.paginate": {"uri":"horizon\/api\/monitoring\/{tag}","methods":["GET","HEAD"]},
        "horizon.monitoring-tag.destroy": {"uri":"horizon\/api\/monitoring\/{tag}","methods":["DELETE"]},
        "horizon.jobs-metrics.index": {"uri":"horizon\/api\/metrics\/jobs","methods":["GET","HEAD"]},
        "horizon.jobs-metrics.show": {"uri":"horizon\/api\/metrics\/jobs\/{id}","methods":["GET","HEAD"]},
        "horizon.queues-metrics.index": {"uri":"horizon\/api\/metrics\/queues","methods":["GET","HEAD"]},
        "horizon.queues-metrics.show": {"uri":"horizon\/api\/metrics\/queues\/{id}","methods":["GET","HEAD"]},
        "horizon.jobs-batches.index": {"uri":"horizon\/api\/batches","methods":["GET","HEAD"]},
        "horizon.jobs-batches.show": {"uri":"horizon\/api\/batches\/{id}","methods":["GET","HEAD"]},
        "horizon.jobs-batches.retry": {"uri":"horizon\/api\/batches\/retry\/{id}","methods":["POST"]},
        "horizon.pending-jobs.index": {"uri":"horizon\/api\/jobs\/pending","methods":["GET","HEAD"]},
        "horizon.completed-jobs.index": {"uri":"horizon\/api\/jobs\/completed","methods":["GET","HEAD"]},
        "horizon.failed-jobs.index": {"uri":"horizon\/api\/jobs\/failed","methods":["GET","HEAD"]},
        "horizon.failed-jobs.show": {"uri":"horizon\/api\/jobs\/failed\/{id}","methods":["GET","HEAD"]},
        "horizon.retry-jobs.show": {"uri":"horizon\/api\/jobs\/retry\/{id}","methods":["POST"]},
        "horizon.jobs.show": {"uri":"horizon\/api\/jobs\/{id}","methods":["GET","HEAD"]},
        "horizon.index": {"uri":"horizon\/{view?}","methods":["GET","HEAD"],"wheres":{"view":"(.*)"}}
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
