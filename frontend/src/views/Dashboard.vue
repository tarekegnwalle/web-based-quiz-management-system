<script setup>
import { ref, onMounted } from 'vue'
import { useAuth } from '../stores/auth'
import api from '../api/axios'

const auth = useAuth()
const stats = ref({
  quizzesCount: 0,
  submissionsCount: 0,
  usersCount: 0
})

const loading = ref(true)

onMounted(async () => {
  try {
    const [quizzes, submissions] = await Promise.all([
      api.get('/quizzes'),
      auth.isStudent.value ? api.get('/my-submissions') : Promise.resolve({ data: [] })
    ])
    
    stats.value.quizzesCount = quizzes.data.length
    stats.value.submissionsCount = submissions.data.length
    
    if (auth.isAdmin.value) {
      const users = await api.get('/users')
      stats.value.usersCount = users.data.length
    }
  } catch (err) {
    console.error('Failed to load dashboard data', err)
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div>
    <header class="mb-10">
      <h1 class="text-4xl font-black text-slate-900 mb-2">Welcome back, {{ auth.state.user?.name }}!</h1>
      <p class="text-slate-500 text-lg">Here's a quick overview of your quiz management system.</p>
    </header>

    <div v-if="loading" class="flex justify-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-600"></div>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
      <!-- Admin/Teacher Stats -->
      <template v-if="auth.isAdmin.value || auth.isTeacher.value">
        <div class="card bg-blue-600 border-blue-600 !p-8">
          <p class="text-blue-100 text-sm font-bold mb-1 uppercase tracking-widest">Active Quizzes</p>
          <h2 class="text-5xl font-black text-white">{{ stats.quizzesCount }}</h2>
        </div>
        
        <div v-if="auth.isAdmin.value" class="card !p-8">
          <p class="text-slate-500 text-sm font-bold mb-1 uppercase tracking-widest">Total Users</p>
          <h2 class="text-5xl font-black text-slate-900">{{ stats.usersCount }}</h2>
        </div>

        <div class="card !p-8">
          <p class="text-slate-500 text-sm font-bold mb-1 uppercase tracking-widest">Global Submissions</p>
          <h2 class="text-5xl font-black text-slate-900">{{ auth.isAdmin.value ? stats.submissionsCount : stats.submissionsCount }}</h2>
        </div>
      </template>

      <!-- Student Stats -->
      <template v-else>
        <div class="card bg-blue-600 border-blue-600 !p-8">
          <p class="text-blue-100 text-sm font-bold mb-1 uppercase tracking-widest">Available Quizzes</p>
          <h2 class="text-5xl font-black text-white">{{ stats.quizzesCount }}</h2>
        </div>
        
        <div class="card !p-8">
          <p class="text-slate-500 text-sm font-bold mb-1 uppercase tracking-widest">Your Attempts</p>
          <h2 class="text-5xl font-black text-slate-900">{{ stats.submissionsCount }}</h2>
        </div>

        <div class="card !p-8">
          <p class="text-slate-500 text-sm font-bold mb-1 uppercase tracking-widest">Avg. Performance</p>
          <h2 class="text-5xl font-black text-slate-900">A+</h2>
        </div>
      </template>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <div class="card">
        <h3 class="text-xl font-bold text-slate-900 mb-8">Quick Actions</h3>
        <div class="space-y-4">
          <router-link v-if="!auth.isStudent.value" to="/quizzes/create" class="flex items-center p-5 bg-slate-50 rounded-2xl border border-slate-100 hover:border-blue-200 hover:bg-blue-50/50 transition-all group">
            <div class="bg-blue-600 text-white p-3 rounded-xl mr-5 shadow-lg shadow-blue-600/20">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
            </div>
            <div>
              <p class="font-bold text-slate-900">Create New Quiz</p>
              <p class="text-sm text-slate-500">Design questions and publish for students</p>
            </div>
          </router-link>

          <router-link to="/quizzes" class="flex items-center p-5 bg-slate-50 rounded-2xl border border-slate-100 hover:border-green-200 hover:bg-green-50/50 transition-all group">
            <div class="bg-green-600 text-white p-3 rounded-xl mr-5 shadow-lg shadow-green-600/20">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
            <div>
              <p class="font-bold text-slate-900">Browse Quizzes</p>
              <p class="text-sm text-slate-500">View and take available assessments</p>
            </div>
          </router-link>

          <router-link v-if="auth.isAdmin.value" to="/admin/users" class="flex items-center p-5 bg-slate-50 rounded-2xl border border-slate-100 hover:border-purple-200 hover:bg-purple-50/50 transition-all group">
            <div class="bg-purple-600 text-white p-3 rounded-xl mr-5 shadow-lg shadow-purple-600/20">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
            </div>
            <div>
              <p class="font-bold text-slate-900">Manage Users</p>
              <p class="text-sm text-slate-500">Add or edit teachers and students</p>
            </div>
          </router-link>
        </div>
      </div>

      <div class="card flex flex-col items-center justify-center text-center p-12">
        <div class="bg-slate-100 p-6 rounded-full mb-6">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <h4 class="text-lg font-bold text-slate-900 mb-2">No Recent Activity</h4>
        <p class="text-slate-500">Activity from you and your students will appear here.</p>
      </div>
    </div>
  </div>
</template>
