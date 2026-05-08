<script setup>
import { ref, onMounted } from 'vue'
import api from '../../api/axios'

const courses = ref([])
const loading = ref(true)
const showModal = ref(false)
const editingCourse = ref(null)

const form = ref({
  name: '',
  code: '',
  year: 1
})

const fetchCourses = async () => {
  try {
    const { data } = await api.get('/courses')
    courses.value = data
  } catch (err) {
    console.error('Failed to fetch courses', err)
  } finally {
    loading.value = false
  }
}

const openModal = (course = null) => {
  if (course) {
    editingCourse.value = course
    form.value = { ...course }
  } else {
    editingCourse.value = null
    form.value = { name: '', code: '', year: 1 }
  }
  showModal.value = true
}

const saveCourse = async () => {
  try {
    if (editingCourse.value) {
      await api.put(`/courses/${editingCourse.value.id}`, form.value)
    } else {
      await api.post('/courses', form.value)
    }
    fetchCourses()
    showModal.value = false
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to save course')
  }
}

const deleteCourse = async (id) => {
  if (!confirm('Are you sure you want to delete this course?')) return
  try {
    await api.delete(`/courses/${id}`)
    fetchCourses()
  } catch (err) {
    alert('Failed to delete course')
  }
}

const importCSV = async (event) => {
  const file = event.target.files[0]
  if (!file) return
  
  const formData = new FormData()
  formData.append('file', file)
  
  try {
    const { data } = await api.post('/courses/import', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    alert(data.message)
    fetchCourses()
  } catch (err) {
    alert('Failed to import courses. Please check your CSV format (headers: name,code,year)')
  }
}

const getYearLabel = (year) => {
  const labels = { 1: '1st Year', 2: '2nd Year', 3: '3rd Year', 4: '4th Year' }
  return labels[year] || year
}

onMounted(fetchCourses)
</script>

<template>
  <div>
    <div class="flex justify-between items-end mb-10">
      <div>
        <h1 class="text-4xl font-black text-slate-900 mb-2">Course Management</h1>
        <p class="text-slate-500 font-medium">Manage the official list of batch courses</p>
      </div>
      <div class="flex space-x-3">
        <input type="file" ref="fileInput" @change="importCSV" class="hidden" accept=".csv">
        <button @click="$refs.fileInput.click()" class="btn-secondary">Upload Course List</button>
        <button @click="openModal()" class="btn-primary">Add New Course</button>
      </div>
    </div>

    <div class="card bg-white shadow-xl border-slate-100 overflow-hidden !p-0">
      <div v-if="loading" class="p-20 text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-600 mx-auto"></div>
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 text-xs uppercase tracking-widest font-black">
              <th class="px-8 py-5">Course Name</th>
              <th class="px-8 py-5">Code</th>
              <th class="px-8 py-5">Target Year</th>
              <th class="px-8 py-5 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="course in courses" :key="course.id" class="hover:bg-slate-50/80 transition-colors">
              <td class="px-8 py-5">
                <p class="text-slate-900 font-bold leading-tight">{{ course.name }}</p>
              </td>
              <td class="px-8 py-5">
                <span class="text-xs font-black text-slate-400 bg-slate-100 px-2.5 py-1 rounded-md">{{ course.code || 'N/A' }}</span>
              </td>
              <td class="px-8 py-5 text-sm text-slate-700 font-bold">
                {{ getYearLabel(course.year) }}
              </td>
              <td class="px-8 py-5 text-right">
                <button @click="openModal(course)" class="text-blue-600 hover:text-blue-700 mr-4 font-bold text-sm">Edit</button>
                <button @click="deleteCourse(course.id)" class="text-red-600 hover:text-red-700 font-bold text-sm">Delete</button>
              </td>
            </tr>
            <tr v-if="courses.length === 0">
              <td colspan="4" class="px-8 py-20 text-center text-slate-400 italic">No courses found. Upload a CSV or add one manually.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
      <div class="card bg-white max-w-md w-full shadow-2xl border-white animate-in zoom-in duration-300">
        <h3 class="text-2xl font-black text-slate-900 mb-8">{{ editingCourse ? 'Edit Course' : 'Add New Course' }}</h3>
        <form @submit.prevent="saveCourse" class="space-y-5">
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Course Name</label>
            <input v-model="form.name" type="text" required class="input" placeholder="e.g. Data Structures">
          </div>
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Course Code</label>
            <input v-model="form.code" type="text" class="input" placeholder="e.g. CS201">
          </div>
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Target Academic Year</label>
            <select v-model="form.year" class="input">
              <option :value="1">1st Year</option>
              <option :value="2">2nd Year</option>
              <option :value="3">3rd Year</option>
              <option :value="4">4th Year</option>
            </select>
          </div>
          <div class="flex space-x-3 pt-8">
            <button @click="showModal = false" type="button" class="btn-secondary flex-grow">Cancel</button>
            <button type="submit" class="btn-primary flex-grow">Save Course</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
