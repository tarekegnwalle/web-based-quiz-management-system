<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../../stores/auth'

const auth = useAuth()
const router = useRouter()

const form = ref({
  email: '',
  password: ''
})

const error = ref('')

const handleLogin = async () => {
  try {
    error.value = ''
    await auth.login(form.value)
    router.push('/dashboard')
  } catch (err) {
    error.value = err.response?.data?.message || 'Login failed. Please check your credentials.'
  }
}
</script>

<template>
  <div class="max-w-md mx-auto mt-20">
    <div class="card bg-white shadow-xl border-slate-100">
      <div class="text-center mb-10">
        <h1 class="text-3xl font-black text-slate-900 mb-3">Welcome to Quiz Management System</h1>
        <p class="text-slate-500 font-medium">Please sign in to your account</p>
      </div>

      <form @submit.prevent="handleLogin" class="space-y-6">
        <div v-if="error" class="bg-red-50 border border-red-100 text-red-600 p-4 rounded-xl text-sm font-bold text-center">
          {{ error }}
        </div>

        <div>
          <label class="block text-sm font-bold text-slate-700 mb-2">Email Address</label>
          <input v-model="form.email" type="email" required class="input" placeholder="name@example.com">
        </div>

        <div>
          <div class="flex justify-between items-center mb-2">
            <label class="block text-sm font-bold text-slate-700">Password</label>
          </div>
          <input v-model="form.password" type="password" required class="input" placeholder="••••••••">
        </div>

        <button type="submit" :disabled="auth.state.loading" class="btn-primary w-full py-4 text-lg">
          {{ auth.state.loading ? 'Signing in...' : 'Sign In' }}
        </button>
      </form>

      <div class="mt-10 text-center text-sm">
        <span class="text-slate-500 font-medium">Don't have an account?</span>
        <router-link to="/register" class="text-blue-600 hover:text-blue-700 font-bold ml-1">Create Account</router-link>
      </div>
    </div>
  </div>
</template>
