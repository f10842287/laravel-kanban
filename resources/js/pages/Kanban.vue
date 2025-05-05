<template>
    <div class="kanban-board">
      <h2>任務列表 Todo-List</h2>
      <!-- <label>搜尋條件：</label>
      <div class="filters">
        <input v-model="searchQuery" placeholder="Search tasks..." />
        <select v-model="selectedLabel">
          <option value="">所有標籤</option>
          <option value="feature">feature</option>
          <option value="design">design</option>
          <option value="bug">bug</option>
          <option value="testing">testing</option>
        </select>
        <select v-model="selectedPriority">
          <option value="">All Priorities</option>
          <option value="high">High</option>
          <option value="normal">Normal</option>
          <option value="low">Low</option>
        </select>
      </div> -->

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

                <!-- 顯示到期日 -->
                <p v-if="element.due_date" class="task-due-date">
                  Due: {{ formatDate(element.due_date) }}
                </p>
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
            <label>任務標題</label>
            <input v-model="newTaskTitle" placeholder="Enter task title" />
            <label>任務描述</label>
            <textarea v-model="newTaskDescription" placeholder="Enter task description"></textarea>
            <label>任務標籤</label>
            <select v-model="newTaskLabel">
              <option value="">選擇標籤</option>
              <option value="feature">feature</option>
              <option value="design">design</option>
              <option value="bug">bug</option>
              <option value="testing">testing</option>
            </select>
            <label>任務優先度</label>
            <select v-model="newTaskPriority">
              <option value="high">高</option>
              <option value="normal">中</option>
              <option value="low">低</option>
            </select>
            <label>截止日期</label>
            <input type="date" v-model="newTaskDueDate" />
            <button @click="addTask(newTaskStatus)" class="btn-add-task">Add Task</button>
            <button @click="closeAddTaskDialog" class="btn-cancel">Cancel</button>
          </div>
        </div>
      </div>
  
      <!-- Edit Task Dialog -->
      <div v-if="isEditTaskDialogOpen" class="overlay" @click="closeEditTaskDialog">
        <div class="task-dialog" @click.stop>
          <div class="dialog-content">
            <label>任務標題</label>
            <input v-model="selectedTask.title" placeholder="Edit task title" />
            <label>任務描述</label>
            <textarea v-model="selectedTask.description" placeholder="Edit description"></textarea>
            <label>任務標籤</label>
            <select v-model="selectedTask.label">
              <option value="feature">feature</option>
              <option value="design">design</option>
              <option value="bug">bug</option>
              <option value="testing">testing</option>
            </select>
            <label>任務優先度</label>
            <select v-model="selectedTask.priority">
              <option value="high">高</option>
              <option value="normal">中</option>
              <option value="low">低</option>
            </select>
            <label>截止日期</label>
            <input type="date" v-model="selectedTask.due_date" />
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
  import { Layers } from "lucide-vue-next";
  import draggable from "vuedraggable";
  
  export default {
    // computed: {
    //   filteredTasks() {
    //     return Object.keys(this.tasks).reduce((acc, status) => {
    //       acc[status] = this.tasks[status].filter(task => {
    //         const matchesSearch = task.title.toLowerCase().includes(this.searchQuery.toLowerCase());
    //         const matchesLabel = this.selectedLabel ? task.label === this.selectedLabel : true;
    //         const matchesPriority = this.selectedPriority ? task.priority === this.selectedPriority : true;
    //         return matchesSearch && matchesLabel && matchesPriority;
    //       });
    //       return acc;
    //     }, {});
    //   },
    // },

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
        newTaskDescription: "",   // new added.
        newTaskLabel: "",     // new added.
        newTaskPriority: "",  // new added.
        searchQuery: '',
        selectedLabel: '',
        selectedPriority: '',
        newTaskDueDate: null,
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
      formatDate(date) {
        const d = new Date(date);
        return d.toLocaleDateString(); // format ex: YYYY/m/d
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
            description: this.newTaskDescription,
            label: this.newTaskLabel,
            priority: this.newTaskPriority,
            due_date: this.newTaskDueDate,
          });

          // 確保返回的任務數據正確
          if (response.data) {
            this.tasks[status].push(response.data);
            this.closeAddTaskDialog();
            // 清除表單
            this.newTaskTitle = "";
            this.newTaskDescription = "";
            this.newTaskLabel = "";
            this.newTaskPriority = "";
            this.newTaskDueDate = null;
          }
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
          const response = await axios.put(`/tasks/${this.selectedTask.id}`, {
            title: this.selectedTask.title,
            description: this.selectedTask.description,
            label: this.selectedTask.label,
            priority: this.selectedTask.priority,
            due_date: this.selectedTask.due_date,
          });

          // 確保成功返回後，更新本地的資料
          if (response.data) {
            const list = this.tasks[this.selectedTask.status];
            const index = list.findIndex((task) => task.id === this.selectedTask.id);
            if (index !== -1) {
              list[index] = response.data;  // 完整替換任務數據
            }
            this.closeEditTaskDialog();
          }
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

  /* 只針對 .task-dialog 裡面的 select 元素 */
  .task-dialog select {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    background-color: #f9f9f9;
    font-size: 16px;
    transition: border-color 0.3s, background-color 0.3s;
    width: 100%;
    margin-bottom: 10px;
  }

  /* 當 select 被選中時 */
  .task-dialog select:focus {
    outline: none;
    border-color: #42a5f5;
    background-color: #fff;
  }

  /* 美化 option 顯示 */
  .task-dialog select option {
    padding: 10px;
    font-size: 16px;
  }

  /* hover 效果 */
  .task-dialog select:hover {
    border-color: #42a5f5;
  }

  /* .filters {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;
    align-items: center;
  }

  .filters input,
  .filters select {
    padding: 10px 12px;
    font-size: 14px;
    border-radius: 6px;
    border: 1px solid #ccc;
    background-color: #fff;
    color: #333;
    outline: none;
    min-width: 160px;
    transition: border-color 0.3s ease;
  }

  .filters input:focus,
  .filters select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.2);
  }

  .filters select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 140 140' xmlns='http://www.w3.org/2000/svg'%3E%3Cpolygon points='70,100 20,40 120,40' fill='%23666'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 10px;
    padding-right: 30px;
  } */



  .task-due-date {
    font-size: 14px;
    color: #d9534f; /* 顯示過期或即將到期的任務時，可以用紅色 */
    margin-top: 5px;
  }

  textarea {
    padding: 12px;
    border-radius: 5px;
    border: 1px solid #ccc;
    font-size: 16px;
  }

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
  