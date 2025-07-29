import { reactive } from 'vue'

export const notification = reactive({
  data: [],
  add(type = 'success', title = 'Success', message = '', timeout = 5) {
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
