export default () => {
  const urlParams = new URLSearchParams(window.location.search)
  const data = {}
  const keys = urlParams.keys()
  const values = urlParams.values()

  for (const key of keys) {
    data[key] = values.next().value
  }

  return data
}
