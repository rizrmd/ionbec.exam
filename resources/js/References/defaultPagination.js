const defaultPaginationObject = () => ({
  "current_page": 1,
  "data": [],
  "first_page_url": null,
  "from": 1,
  "last_page": 1,
  "last_page_url": null,
  "links": [
    {
      "url": null,
      "label": "&laquo; Previous",
      "active": false
    }, {
      "url": null,
      "label": "1",
      "active": true
    }, {
      "url": null,
      "label": "Next &raquo;",
      "active": false
    }
  ],
  "next_page_url": null,
  "path": null,
  "per_page": 15,
  "prev_page_url": null,
  "to": 15,
  "total": 0
})

export default new defaultPaginationObject()
