<script setup>
import { ref, onMounted } from 'vue'
import api from '../../api/axios'

const users = ref([])
const loading = ref(true)
const showModal = ref(false)
const editingUser = ref(null)

const form = ref({
  name: '',
  email: '',
  password: '',
  role: 'student',
  year: 1,
  status: 'active'
})

const fetchUsers = async () => {
  try {
    const { data } = await api.get('/users')
    users.value = data
  } catch (err) {
    console.error('Failed to fetch users', err)
  } finally {
    loading.value = false
  }
}

const openModal = (user = null) => {
  if (user) {
    editingUser.value = user
    form.value = { ...user, password: '' }
  } else {
    editingUser.value = null
    form.value = { name: '', email: '', password: '', role: 'student', year: 1, status: 'active' }
  }
  showModal.value = true
}

const saveUser = async () => {
  try {
    if (editingUser.value) {
      await api.put(`/users/${editingUser.value.id}`, form.value)
    } else {
      await api.post('/users', form.value)
    }
    fetchUsers()
    showModal.value = false
  } catch (err) {
    alert('Failed to save user')
  }
}

const deleteUser = async (id) => {
  if (!confirm('Are you sure you want to delete this user?')) return
  try {
    await api.delete(`/users/${id}`)
    fetchUsers()
  } catch (err) {
    alert('Failed to delete user')
  }
}

const importCSV = async (event) => {
  const file = event.target.files[0]
  if (!file) return
  
  const formData = new FormData()
  formData.append('file', file)
  
  try {
    const { data } = await api.post('/users/import', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    alert(data.message + '. These users are now in "Pending" status.')
    fetchUsers()
  } catch (err) {
    alert('Failed to import users. Please check your CSV format (headers: name,email,password,role,year)')
  }
}

const activateUser = (user) => {
  openModal(user)
  form.value.status = 'active'
}

const getYearLabel = (year) => {
  if (!year) return '-'
  const labels = { 1: '1st Year', 2: '2nd Year', 3: '3rd Year', 4: '4th Year' }
  return labels[year] || year
}

onMounted(fetchUsers)
</script>

<template>
  <div>
    <div class="flex justify-between items-end mb-10">
      <div>
        <h1 class="text-4xl font-black text-slate-900 mb-2">User Management</h1>
        <p class="text-slate-500 font-medium">Manage student rosters and system access</p>
      </div>
      <div class="flex space-x-3">
        <input type="file" ref="fileInput" @change="importCSV" class="hidden" accept=".csv">
        <button @click="$refs.fileInput.click()" class="btn-secondary">Upload Batch List</button>
        <button @click="openModal()" class="btn-primary">Add Individual User</button>
      </div>
    </div>

    <div class="card bg-white shadow-xl border-slate-100 overflow-hidden !p-0">
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 text-xs uppercase tracking-widest font-black">
              <th class="px-8 py-5">User</th>
              <th class="px-8 py-5">Role</th>
              <th class="px-8 py-5">Academic Year</th>
              <th class="px-8 py-5">Status</th>
              <th class="px-8 py-5 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="user in users" :key="user.id" class="hover:bg-slate-50/80 transition-colors">
              <td class="px-8 py-5">
                <div class="flex items-center space-x-4">
                  <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center font-black text-slate-600">
                    {{ user.name.charAt(0).toUpperCase() }}
                  </div>
                  <div>
                    <p class="text-slate-900 font-bold leading-tight">{{ user.name }}</p>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">{{ user.email }}</p>
                  </div>
                </div>
              </td>
              <td class="px-8 py-5">
                <span :class="`badge-${user.role}`">{{ user.role }}</span>
              </td>
              <td class="px-8 py-5 text-sm text-slate-700 font-bold">
                {{ user.role === 'student' ? getYearLabel(user.year) : '-' }}
              </td>
              <td class="px-8 py-5">
                <span v-if="user.status === 'active'" class="text-[10px] font-black text-green-600 bg-green-50 px-2.5 py-1 rounded-full uppercase tracking-widest border border-green-100">Active</span>
                <span v-else class="text-[10px] font-black text-amber-600 bg-amber-50 px-2.5 py-1 rounded-full uppercase tracking-widest border border-amber-100">Pending</span>
              </td>
              <td class="px-8 py-5 text-right">
                <button v-if="user.status === 'pending'" @click="activateUser(user)" class="text-green-600 hover:text-green-700 mr-4 font-black text-sm">Grant Access</button>
                <button @click="openModal(user)" class="text-blue-600 hover:text-blue-700 mr-4 font-bold text-sm">Edit</button>
                <button @click="deleteUser(user.id)" class="text-red-600 hover:text-red-700 font-bold text-sm">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
      <div class="card bg-white max-w-md w-full shadow-2xl border-white animate-in zoom-in duration-300">
        <h3 class="text-2xl font-black text-slate-900 mb-8">{{ editingUser ? (editingUser.status === 'pending' ? 'Grant Access' : 'Edit User') : 'Add New User' }}</h3>
        <form @submit.prevent="saveUser" class="space-y-5">
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Full Name</label>
            <input v-model="form.name" type="text" required class="input" placeholder="John Doe">
          </div>
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Email Address (Username)</label>
            <input v-model="form.email" type="email" required class="input" placeholder="john@example.com">
          </div>
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Password {{ editingUser ? '(Leave blank to keep current)' : '' }}</label>
            <input v-model="form.password" type="password" :required="!editingUser" class="input" placeholder="••••••••">
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-bold text-slate-700 mb-2">Role</label>
              <select v-model="form.role" class="input">
                <option value="student">Student</option>
                <option value="teacher">Teacher</option>
                <option value="admin">Administrator</option>
              </select>
            </div>
            <div v-if="form.role === 'student'">
              <label class="block text-sm font-bold text-slate-700 mb-2">Year</label>
              <select v-model="form.year" class="input">
                <option :value="1">1st Year</option>
                <option :value="2">2nd Year</option>
                <option :value="3">3rd Year</option>
                <option :value="4">4th Year</option>
              </select>
            </div>
          </div>
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Access Status</label>
            <select v-model="form.status" class="input">
              <option value="active">Active (Can Login)</option>
              <option value="pending">Pending (Restricted)</option>
            </select>
          </div>
          <div class="flex space-x-3 pt-8">
            <button @click="showModal = false" type="button" class="btn-secondary flex-grow">Cancel</button>
            <button type="submit" class="btn-primary flex-grow">{{ editingUser?.status === 'pending' ? 'Activate Account' : 'Save Changes' }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
