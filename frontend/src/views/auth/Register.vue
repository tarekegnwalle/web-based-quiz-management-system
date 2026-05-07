<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../../stores/auth'

const auth = useAuth()
const router = useRouter()

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 'student'
})

const errors = ref({})

const handleRegister = async () => {
  try {
    errors.value = {}
    await auth.register(form.value)
    router.push('/dashboard')
  } catch (err) {
    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
    } else {
      errors.value = { message: [err.response?.data?.message || 'Registration failed'] }
    }
  }
}
</script>

<template>
  <div class="max-w-md mx-auto mt-12">
    <div class="card bg-white shadow-xl border-slate-100">
      <div class="text-center mb-10">
        <h1 class="text-3xl font-black text-slate-900 mb-3">Create Account</h1>
        <p class="text-slate-500 font-medium">Join the Quiz Management System</p>
      </div>

      <form @submit.prevent="handleRegister" class="space-y-4">
        <div>
          <label class="block text-sm font-bold text-slate-700 mb-2">Full Name</label>
          <input v-model="form.name" type="text" required class="input" placeholder="John Doe">
          <p v-if="errors.name" class="text-red-600 text-xs mt-1 font-bold">{{ errors.name[0] }}</p>
        </div>

        <div>
          <label class="block text-sm font-bold text-slate-700 mb-2">Email Address</label>
          <input v-model="form.email" type="email" required class="input" placeholder="name@example.com">
          <p v-if="errors.email" class="text-red-600 text-xs mt-1 font-bold">{{ errors.email[0] }}</p>
        </div>

        <div>
          <label class="block text-sm font-bold text-slate-700 mb-2">I am a...</label>
          <select v-model="form.role" class="input">
            <option value="student">Student</option>
            <option value="teacher">Teacher</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-bold text-slate-700 mb-2">Password</label>
          <input v-model="form.password" type="password" required class="input" placeholder="••••••••">
          <p v-if="errors.password" class="text-red-600 text-xs mt-1 font-bold">{{ errors.password[0] }}</p>
        </div>

        <div>
          <label class="block text-sm font-bold text-slate-700 mb-2">Confirm Password</label>
          <input v-model="form.password_confirmation" type="password" required class="input" placeholder="••••••••">
        </div>

        <button type="submit" :disabled="auth.state.loading" class="btn-primary w-full py-4 text-lg mt-6">
          {{ auth.state.loading ? 'Creating Account...' : 'Sign Up' }}
        </button>
      </form>

      <div class="mt-10 text-center text-sm">
        <span class="text-slate-500 font-medium">Already have an account?</span>
        <router-link to="/login" class="text-blue-600 hover:text-blue-700 font-bold ml-1">Sign In</router-link>
      </div>
    </div>
  </div>
</template>
