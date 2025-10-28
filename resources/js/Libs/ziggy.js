import ziggyRoute from 'ziggy-js'
import { Ziggy } from '../ziggy'

const route = (name, params = undefined, absolute = false) => {
  // Handle undefined route names gracefully
  if (!name) {
    return '#'
  }
  
  // Always force relative URLs by setting absolute to false
  const result = ziggyRoute(name, params, false, Ziggy)
  
  // If result still has absolute URL, convert to relative
  if (result && (result.startsWith('http://') || result.startsWith('https://'))) {
    const url = new URL(result)
    return url.pathname + url.search + url.hash
  }
  
  return result
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
