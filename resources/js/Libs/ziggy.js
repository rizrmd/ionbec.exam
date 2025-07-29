import ziggyRoute from 'ziggy-js'

const route = (name, params = undefined, absolute = undefined) => {
  return ziggyRoute(name, params, absolute, Ziggy)
}

export default route

export const Plugin = {
  install(app) {
    app.config.globalProperties.route = route
    window.route = route

    app.mixin({
      methods: {
        route,
      }
    })
  }
}
