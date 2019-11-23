<template>
   <div class="col-md-4 col-md-offset-4">
    <h1>Todo List:</h1>

    <div v-if="message != null" class="alert alert-primary" role="alert">
      {{ message }}
    </div>

    <table class="table table-striped">
        <tbody>
        <tr>
            <th>#</th><th>User</th><th>Description</th><th></th><th></th>
        </tr>
        <tr v-for="(todo, index) in todoList" :key="index" class="entry">
            <td>{{ todo.id }}</td>
            <td>{{ todo.user_id }}</td>
            <td v-bind:class="(todo.completed == 1) ? 'completed':''">
                {{ todo.description }}
            </td>
            <td>
                <button v-if="todo.completed == 0" @click.prevent="markItemCompleted(todo.id)" class="btn btn-xs btn-primary"><span class="glyphicon glyphicon-ok glyphicon-green"></span></button>
            </td>
            <td>
                <button @click.prevent="deleteItem(todo.id)" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-remove glyphicon-white"></span></button>
            </td>
        <tr>
        <tr>
            <td colspan="3">
                <input type="textbox" name="description" class="small-6 small-center" placeholder="Description..." v-model="description">
            </td>
            <td colspan="2">
                <button @click.prevent="addItem()"  class="btn btn-sm btn-primary">Add</button>
            </td>
        </tr>
        </tbody>
    </table>
  </div>
</template>

<script>

export default {
    name: 'app',
    components: {
    },
    data() {
        return {
            todoList: [],
            description: "",
            message:null,
        };
    },
    mounted: async function() {
        this.todoList = await this.launchRequest("/todos/json","GET");
    },
    methods:{
        deleteItem: async function(id){
            await this.launchRequest("/todo/delete/"+id,"GET");
            this.message = "The todo:"+id+" has been removed.";
            this.todoList = await this.launchRequest("/todos/json","GET");
        },
        markItemCompleted: async function(id){
            await this.launchRequest("/todo/complete/"+id,"GET");
            this.message = "The todo:"+id+' has been marked as completed.';
            this.todoList = await this.launchRequest("/todos/json","GET");
        },
        addItem: async function(){
            if(this.description == ""){
                this.message = "The description can not be empty";
                return;
            }
            this.message = null;
            await this.launchRequest("/todo/add","POST",{description:this.description});
            this.message = "The todo:"+this.description + " has been added";
            this.description = "";
            this.todoList = await this.launchRequest("/todos/json","GET");
        },
    }
}
</script>

<style scoped>
#app {
 
}
</style>
