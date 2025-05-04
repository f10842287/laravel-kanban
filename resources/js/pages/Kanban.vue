<template>
    <div class="kanban-board">
      <h2>任務列表 Todo-List</h2>
  
      <div class="board">
        <div v-for="status in statuses" :key="status" class="status-column">
          <h3>{{ status }}</h3>
          <draggable
            v-model="tasks[status]"
            :group="{ name: 'tasks', pull: true, put: true }"
            :itemKey="'id'"
            @change="onDragChange(status)">
            <template #item="{ element }">
              <div
                class="task"
                :class="{ completed: element.status === 'done' }"
                @click="openTaskEditor(element)">
                <p>{{ element.title }}</p>
              </div>
            </template>
          </draggable>
          <button @click="openAddTaskDialog(status)">Add Task</button>
        </div>
      </div>
  
      <!-- Add Task Dialog -->
      <div v-if="isAddTaskDialogOpen" class="overlay" @click="closeAddTaskDialog">
        <div class="task-dialog" @click.stop>
          <div class="dialog-content">
            <input v-model="newTaskTitle" placeholder="Enter task title" />
            <button @click="addTask(newTaskStatus)" class="btn-add-task">Add Task</button>
            <button @click="closeAddTaskDialog" class="btn-cancel">Cancel</button>
          </div>
        </div>
      </div>
  
      <!-- Edit Task Dialog -->
      <div v-if="isEditTaskDialogOpen" class="overlay" @click="closeEditTaskDialog">
        <div class="task-dialog" @click.stop>
          <div class="dialog-content">
            <input v-model="selectedTask.title" placeholder="Edit task title" />
            <button @click="updateTask" class="btn-save">Save</button>
            <button @click="deleteTask" class="btn-delete">Delete</button>
            <button @click="closeEditTaskDialog" class="btn-cancel">Cancel</button>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import axios from "axios";
  import draggable from "vuedraggable";
  
  export default {
    components: {
      draggable,
    },
    data() {
      return {
        statuses: ["todo", "doing", "review", "done"],
        tasks: {
          todo: [],
          doing: [],
          review: [],
          done: [],
        },
        newTaskTitle: "",
        newTaskStatus: "todo",
        isAddTaskDialogOpen: false,
        isEditTaskDialogOpen: false,
        selectedTask: null,
      };
    },
    mounted() {
      this.fetchTasks();
    },
    methods: {
      async fetchTasks() {
        try {
          const response = await axios.get("/tasks");
          this.tasks = {
            todo: [],
            doing: [],
            review: [],
            done: [],
          };
          response.data.forEach((task) => {
            this.tasks[task.status].push(task);
          });
        } catch (error) {
          console.error("Error fetching tasks:", error);
        }
      },
      openAddTaskDialog(status) {
        this.newTaskStatus = status;
        this.isAddTaskDialogOpen = true;
      },
      closeAddTaskDialog() {
        this.isAddTaskDialogOpen = false;
        this.newTaskTitle = "";
      },
      async addTask(status) {
        if (this.newTaskTitle.trim() === "") return;
        try {
          const response = await axios.post("/tasks", {
            title: this.newTaskTitle,
            status,
          });
          this.tasks[status].push(response.data);
          this.closeAddTaskDialog();
        } catch (error) {
          console.error("Error adding task:", error);
        }
      },
      openTaskEditor(task) {
        this.selectedTask = { ...task };
        this.isEditTaskDialogOpen = true;
      },
      closeEditTaskDialog() {
        this.isEditTaskDialogOpen = false;
        this.selectedTask = null;
      },
      async updateTask() {
        if (this.selectedTask?.title.trim() === "") return;
        try {
          await axios.put(`/tasks/${this.selectedTask.id}`, {
            title: this.selectedTask.title,
          });
          const list = this.tasks[this.selectedTask.status];
          const index = list.findIndex((task) => task.id === this.selectedTask.id);
          if (index !== -1) list[index].title = this.selectedTask.title;
          this.closeEditTaskDialog();
        } catch (error) {
          console.error("Error updating task:", error);
        }
      },
      async deleteTask() {
        try {
          await axios.delete(`/tasks/${this.selectedTask.id}`);
          const list = this.tasks[this.selectedTask.status];
          const index = list.findIndex((task) => task.id === this.selectedTask.id);
          if (index !== -1) list.splice(index, 1);
          this.closeEditTaskDialog();
        } catch (error) {
          console.error("Error deleting task:", error);
        }
      },
      async onDragChange(newStatus) {
        const tasks = this.tasks[newStatus];
        for (let task of tasks) {
          if (task.status !== newStatus) {
            try {
              await axios.put(`/tasks/${task.id}/status`, {
                status: newStatus,
              });
              task.status = newStatus;
            } catch (error) {
              console.error("Error updating task status:", error);
            }
          }
        }
      },
    },
  };
  </script>
  
  <style scoped>
  .kanban-board {
    max-width: 1200px;
    margin: 0 auto;
    padding: 30px;
  }
  
  h2 {
    text-align: center;
    font-size: 36px;
    color: #333;
    margin-bottom: 30px;
  }
  
  .board {
    display: flex;
    justify-content: space-between;
    gap: 20px;
  }
  
  .status-column {
    width: 22%;
    padding: 20px;
    background: linear-gradient(145deg, #f0f0f0, #e0e0e0);
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }
  
  .status-column h3 {
    text-align: center;
    font-size: 24px;
    color: #555;
    margin-bottom: 20px;
  }
  
  .task {
    background-color: #ffffff;
    padding: 15px;
    margin: 10px 0;
    border-radius: 5px;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease-in-out;
  }
  
  .task:hover {
    transform: translateY(-4px);
  }
  
  .completed {
    background-color: #d3f9d8;
  }
  
  button {
    display: block;
    width: 100%;
    padding: 10px;
    margin-top: 15px;
    border-radius: 5px;
    border: none;
    background-color: #007bff;
    color: white;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }
  
  button:hover {
    background-color: #0056b3;
  }
  
  .task-dialog {
    position: relative;
    background-color: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    width: 350px;
    max-width: 90%;
    z-index: 9999;
  }
  
  .dialog-content {
    display: flex;
    flex-direction: column;
    gap: 15px;
  }
  
  input {
    padding: 12px;
    border-radius: 5px;
    border: 1px solid #ccc;
    font-size: 16px;
  }
  
  .btn-add-task,
  .btn-save {
    background-color: #28a745;
    color: white;
  }
  
  .btn-delete {
    background-color: #dc3545;
    color: white;
  }
  
  .btn-cancel {
    background-color: #6c757d;
    color: white;
  }
  
  .overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9998;
    display: flex;
    justify-content: center;
    align-items: center;
  }
  </style>
  