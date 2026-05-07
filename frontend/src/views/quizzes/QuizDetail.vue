<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../api/axios'

const route = useRoute()
const quiz = ref(null)
const submissions = ref([])
const loading = ref(true)

const fetchData = async () => {
  try {
    const [qRes, sRes] = await Promise.all([
      api.get(`/quizzes/${route.params.id}`),
      api.get(`/quizzes/${route.params.id}/submissions`)
    ])
    quiz.value = qRes.data
    submissions.value = sRes.data
  } catch (err) {
    console.error('Failed to load quiz details', err)
  } finally {
    loading.value = false
  }
}

onMounted(fetchData)
</script>

<template>
  <div class="max-w-6xl mx-auto">
    <div v-if="loading" class="flex justify-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-600"></div>
    </div>

    <template v-else-if="quiz">
      <header class="mb-12">
        <router-link to="/quizzes" class="text-blue-600 hover:text-blue-700 font-bold flex items-center mb-6">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Back to Assessments
        </router-link>
        <h1 class="text-5xl font-black text-slate-900 mb-6">{{ quiz.title }}</h1>
        <div class="flex items-center space-x-8 text-slate-500 font-semibold">
          <span class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Created {{ new Date(quiz.created_at).toLocaleDateString() }}
          </span>
          <span class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            {{ quiz.questions.length }} Questions
          </span>
        </div>
      </header>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <div class="lg:col-span-2 space-y-8">
          <h2 class="text-2xl font-black text-slate-900 flex items-center">
            Student Performance
            <span class="ml-4 bg-slate-100 text-slate-600 text-xs px-2.5 py-1 rounded-lg font-bold">{{ submissions.length }}</span>
          </h2>
          
          <div v-if="submissions.length === 0" class="card text-center py-20 bg-slate-50 border-dashed border-slate-200">
            <p class="text-slate-400 font-medium italic">No students have completed this quiz yet.</p>
          </div>
          
          <div v-else class="space-y-6">
            <div v-for="sub in submissions" :key="sub.id" class="card !p-0 overflow-hidden hover:border-blue-200 transition-all">
              <div class="flex items-center justify-between p-6 bg-white">
                <div class="flex items-center space-x-5">
                  <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center font-black text-xl border border-blue-100">
                    {{ sub.user.name.charAt(0).toUpperCase() }}
                  </div>
                  <div>
                    <p class="font-black text-slate-900 text-lg leading-tight">{{ sub.user.name }}</p>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">{{ new Date(sub.created_at).toLocaleString() }}</p>
                  </div>
                </div>
                <div class="text-right bg-slate-50 px-6 py-3 rounded-2xl border border-slate-100 min-w-[100px]">
                  <p class="text-3xl font-black text-blue-600 leading-none mb-1">{{ sub.score }}</p>
                  <p class="text-[10px] text-slate-400 uppercase font-black tracking-widest">Points</p>
                </div>
              </div>
              <!-- Student Comment Section -->
              <div v-if="sub.comment" class="px-6 py-4 bg-blue-50/30 border-t border-blue-50">
                <p class="text-[10px] font-black text-blue-600 uppercase tracking-widest mb-1">Student Comment</p>
                <p class="text-slate-700 text-sm italic font-medium leading-relaxed">"{{ sub.comment }}"</p>
              </div>
            </div>
          </div>
        </div>

        <div class="space-y-8">
          <h2 class="text-2xl font-black text-slate-900">Quiz Content</h2>
          <div class="space-y-4">
            <div v-for="(q, i) in quiz.questions" :key="q.id" class="card !p-5 bg-slate-50 border-slate-100">
              <div class="flex justify-between items-start mb-2">
                <p class="text-[10px] font-black text-blue-600 uppercase tracking-widest">Question {{ i + 1 }}</p>
                <span class="text-[10px] font-bold text-slate-400 bg-white px-2 py-0.5 rounded-full border border-slate-100">{{ q.points }} pts</span>
              </div>
              <p class="text-slate-800 font-bold leading-relaxed line-clamp-3">{{ q.question_text }}</p>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
