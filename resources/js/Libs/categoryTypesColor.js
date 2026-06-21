export default (name, colorMap = null) => {
  if (colorMap && colorMap[name]) {
    return colorMap[name]
  }

  const map = {
    'disease-group': 'red',
    'region-group': 'green',
    'specific-part': 'blue',
    'typical-group': 'yellow'
  }

  return map[name] ?? 'gray'
}
