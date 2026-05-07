<script setup>
import { ref, onMounted } from 'vue'
import { useAuth } from '../../stores/auth'
import api from '../../api/axios'

const auth = useAuth()
const quizzes = ref([])
const loading = ref(true)

const fetchQuizzes = async () => {
  try {
    const { data } = await api.get('/quizzes')
    quizzes.value = data
  } catch (err) {
    console.error('Error fetching quizzes', err)
  } finally {
    loading.value = false
  }
}

const deleteQuiz = async (id) => {
  if (!confirm('Are you sure you want to delete this quiz?')) return
  try {
    await api.delete(`/quizzes/${id}`)
    quizzes.value = quizzes.value.filter(q => q.id !== id)
  } catch (err) {
    alert('Failed to delete quiz')
  }
}

onMounted(fetchQuizzes)
</script>

<template>
  <div>
    <div class="flex justify-between items-end mb-10">
      <div>
        <h1 class="text-4xl font-black text-slate-900 mb-2">Quizzes</h1>
        <p class="text-slate-500 font-medium">Explore and manage available assessments</p>
      </div>
      <router-link v-if="!auth.isStudent.value" to="/quizzes/create" class="btn-primary">
        Create New Quiz
      </router-link>
    </div>

    <div v-if="loading" class="flex justify-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary-500"></div>
    </div>

    <div v-else-if="quizzes.length === 0" class="card text-center py-20">
      <div class="text-gray-600 mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
      </div>
      <h3 class="text-xl font-bold text-gray-400">No quizzes found</h3>
      <p class="text-gray-600 mt-2">Get started by creating your first quiz assessment.</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="quiz in quizzes" :key="quiz.id" class="card group hover:border-primary-500/50 transition-all">
        <div class="flex justify-between items-start mb-4">
          <span class="bg-primary-500/10 text-primary-400 text-[10px] uppercase tracking-widest font-bold px-2 py-1 rounded">
            {{ quiz.questions?.length || 0 }} Questions
          </span>
          <div v-if="auth.isAdmin.value || (auth.isTeacher.value && quiz.creator_id === auth.state.user.id)" class="flex space-x-2">
            <button @click="deleteQuiz(quiz.id)" class="text-gray-600 hover:text-red-500 transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>

        <h3 class="text-xl font-bold text-slate-900 mb-2 group-hover:text-blue-600 transition-colors">{{ quiz.title }}</h3>
        <p class="text-slate-600 text-sm mb-6 line-clamp-2 leading-relaxed">{{ quiz.description || 'No description provided.' }}</p>

        <div class="flex items-center justify-between mt-auto pt-4 border-t border-gray-800">
          <div class="flex items-center space-x-2">
            <div class="w-8 h-8 rounded-full bg-gray-800 flex items-center justify-center text-xs font-bold text-gray-400">
              {{ quiz.creator?.name?.charAt(0).toUpperCase() }}
            </div>
            <span class="text-xs text-gray-400">{{ quiz.creator?.name }}</span>
          </div>
          
          <router-link v-if="auth.isStudent.value" :to="`/quizzes/${quiz.id}/take`" class="btn-primary py-1.5 px-3 text-xs">
            Start Quiz
          </router-link>
          <router-link v-else :to="`/quizzes/${quiz.id}`" class="text-primary-500 hover:text-primary-400 text-xs font-bold">
            View Details
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>
