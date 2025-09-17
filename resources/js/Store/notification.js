import { reactive } from 'vue'

export const notification = reactive({
  data: [],
  lastNotification: {},  // Track last notification by message
  add(type = 'success', title = 'Success', message = '', timeout = 5) {
    // Throttle duplicate notifications - only show if 3 seconds have passed
    const notificationKey = `${type}-${title}-${message}`;
    const now = Date.now();
    if (this.lastNotification[notificationKey] && 
        (now - this.lastNotification[notificationKey]) < 3000) {
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
