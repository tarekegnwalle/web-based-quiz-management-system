<script setup>
import { useRouter, useRoute } from 'vue-router'
import { useAuth } from './stores/auth'

const auth = useAuth()
const router = useRouter()
const route = useRoute()

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

const navLinks = [
  { name: 'Dashboard', path: '/dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', role: 'all' },
  { name: 'Quizzes', path: '/quizzes', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', role: 'all' },
  { name: 'Users', path: '/admin/users', icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', role: 'admin' },
  { name: 'Courses', path: '/admin/courses', icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.168 0.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332 0.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332 0.477-4.5 1.253', role: 'admin' },
]

const isLinkVisible = (link) => {
  if (link.role === 'all') return true
  return auth.state.user?.role === link.role
}
</script>

<template>
  <div class="min-h-screen flex bg-slate-50">
    <!-- Sidebar -->
    <aside v-if="auth.isAuthenticated.value" class="w-72 bg-blue-700 text-white flex flex-col fixed inset-y-0 shadow-2xl z-50">
      <div class="p-8">
        <div class="flex items-center space-x-3 mb-10">
          <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-md border border-white/30">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.168 0.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332 0.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332 0.477-4.5 1.253" />
            </svg>
          </div>
          <span class="text-2xl font-black tracking-tighter">QuizMaster</span>
        </div>

        <nav class="space-y-2">
          <template v-for="link in navLinks" :key="link.path">
            <router-link v-if="isLinkVisible(link)" :to="link.path" 
              class="flex items-center space-x-4 px-4 py-4 rounded-2xl transition-all font-bold group"
              :class="route.path === link.path ? 'bg-white text-blue-700 shadow-lg' : 'text-blue-100 hover:bg-white/10'">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="link.icon" />
              </svg>
              <span>{{ link.name }}</span>
            </router-link>
          </template>
        </nav>
      </div>

      <div class="mt-auto p-8 border-t border-white/10">
        <div class="flex items-center space-x-4 mb-6">
          <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center font-black text-white text-xl border border-white/30">
            {{ auth.state.user?.name.charAt(0).toUpperCase() }}
          </div>
          <div class="overflow-hidden">
            <p class="font-black text-white leading-tight truncate">{{ auth.state.user?.name }}</p>
            <p class="text-xs text-blue-200 capitalize font-medium">{{ auth.state.user?.role }}</p>
          </div>
        </div>
        <button @click="handleLogout" class="w-full flex items-center justify-center space-x-3 py-4 bg-red-500/20 hover:bg-red-500 text-white rounded-2xl transition-all font-bold group border border-red-500/30">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
          <span>Logout</span>
        </button>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-grow flex flex-col" :class="{ 'ml-72': auth.isAuthenticated.value }">
      <main class="flex-grow p-10 max-w-[1600px] mx-auto w-full">
        <router-view />
      </main>

      <footer class="p-10 text-center text-slate-400 text-sm border-t border-slate-100">
        &copy; 2026 QuizMaster Management System. All rights reserved.
      </footer>
    </div>
  </div>
</template>

<style>
/* Smooth transitions for navigation */
.router-link-active {
  transform: translateX(4px);
}
</style>
