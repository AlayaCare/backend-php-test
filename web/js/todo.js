Vue.use(VueTables.TodoTable);

new Vue({
  el: "#app",
  delimiters: ['${', '}'],
  data: {
    columns: ['id', 'title', 'status', 'updated', 'url', 'delete'],
    data: todo,
    options: {
      headings: {
        id: '#',
        title: 'Title',
        description: 'Description'
      },
      sortable: ['id', 'title', 'status', 'updated'],
      filterable: ['title', 'status', 'description']
    }

  }
});
