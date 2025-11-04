import { reactive } from 'vue'

export const notification = reactive({
  data: [],
  lastNotification: {},  // Track last notification by message
  add(type = 'success', title = 'Success', message = '', timeout = 5) {
    // 🔒 FIXED: Less aggressive throttling for answer saving notifications
    // Allow answer save notifications to show more frequently (1 second cooldown)
    const isAnswerSaveNotification = title === 'Success' && message.includes('saved');
    const throttleTime = isAnswerSaveNotification ? 1000 : 3000; // 1s for answers, 3s for others

    // Throttle duplicate notifications - but allow answer save notifications more frequently
    const notificationKey = `${type}-${title}-${message}`;
    const now = Date.now();
    if (this.lastNotification[notificationKey] &&
        (now - this.lastNotification[notificationKey]) < throttleTime) {
      return; // Skip showing notification if shown recently
    }
    this.lastNotification[notificationKey] = now;

    const id = Math.random().toString(36).substring(2, 10) + Math.random().toString(36).substring(2, 10);
    this.data.push({
      id: id,
      type: type,
      title: title,
      message: message,
    });

    setTimeout(() => {
      const index = this.data.findIndex(notif => notif.id === id);
      if (index >= 0) {
        this.data.splice(index, 1);
      }
    }, timeout * 1000)
  },
  remove(id) {
    const index = this.data.findIndex(notif => notif.id === id);
    if (index >= 0) {
      this.data.splice(index, 1);
    }
  }
})
