<script setup>
import { useRouter } from 'vue-router'
import { useAuth } from './stores/auth'

const auth = useAuth()
const router = useRouter()

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}
</script>

<template>
  <div class="min-h-screen flex flex-col bg-slate-50">
    <nav v-if="auth.isAuthenticated.value" class="bg-white border-b border-slate-200 py-4 shadow-sm">
      <div class="container mx-auto px-4 flex justify-between items-center">
        <router-link to="/dashboard" class="text-2xl font-black text-blue-600">QuizMaster</router-link>
        
        <div class="flex items-center space-x-8 text-slate-600">
          <router-link to="/quizzes" class="font-medium hover:text-blue-600 transition-colors">Quizzes</router-link>
          <router-link v-if="auth.isAdmin.value" to="/admin/users" class="font-medium hover:text-blue-600 transition-colors">Users</router-link>
          
          <div class="flex items-center space-x-4 border-l border-slate-200 pl-8">
            <div class="text-right">
              <p class="text-sm font-bold text-slate-900">{{ auth.state.user?.name }}</p>
              <p class="text-xs text-slate-500 capitalize">{{ auth.state.user?.role }}</p>
            </div>
            <button @click="handleLogout" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-xl transition-all flex items-center space-x-2 border border-slate-200">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
              <span class="text-sm font-bold">Logout</span>
            </button>
          </div>
        </div>
      </div>
    </nav>

    <main class="flex-grow container mx-auto px-4 py-12">
      <router-view />
    </main>

    <footer class="bg-white border-t border-slate-200 py-8 text-center text-slate-400 text-sm">
      &copy; 2026 QuizMaster Management System. All rights reserved.
    </footer>
  </div>
</template>
