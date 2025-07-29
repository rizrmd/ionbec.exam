import route from "@/Libs/ziggy";

export default (name = null, params = null, values = null) => {
  if (name && params && values) {
    let resultParams = {};
    const valuesInArray = values.split(',');
    params.split(',').forEach((param, index) => {
      if (valuesInArray[index] !== 'undefined') {
        resultParams[param] = valuesInArray[index]
      }
    })
    return route(name, resultParams)
  }
  return null;
}
