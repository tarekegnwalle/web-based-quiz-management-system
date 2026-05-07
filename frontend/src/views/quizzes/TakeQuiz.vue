<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../api/axios'

const route = useRoute()
const router = useRouter()
const quiz = ref(null)
const answers = ref({})
const comment = ref('')
const loading = ref(true)
const submitting = ref(false)
const result = ref(null)

const fetchQuiz = async () => {
  try {
    const { data } = await api.get(`/quizzes/${route.params.id}`)
    quiz.value = data
  } catch (err) {
    alert('Failed to load quiz')
    router.push('/quizzes')
  } finally {
    loading.value = false
  }
}

const progress = computed(() => {
  if (!quiz.value) return 0
  const answered = Object.keys(answers.value).length
  return Math.round((answered / quiz.value.questions.length) * 100)
})

const submitQuiz = async () => {
  if (Object.keys(answers.value).length < quiz.value.questions.length) {
    if (!confirm('You have unanswered questions. Submit anyway?')) return
  }
  
  submitting.value = true
  try {
    const { data } = await api.post(`/quizzes/${quiz.value.id}/submit`, { 
      answers: answers.value,
      comment: comment.value 
    })
    result.value = data
  } catch (err) {
    alert(err.response?.data?.message || 'Submission failed')
  } finally {
    submitting.value = false
  }
}

onMounted(fetchQuiz)
</script>

<template>
  <div class="max-w-4xl mx-auto pb-20">
    <div v-if="loading" class="flex justify-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-600"></div>
    </div>

    <template v-else-if="result">
      <div class="card text-center py-16 bg-white shadow-2xl border-white animate-in zoom-in duration-500">
        <div class="w-28 h-28 bg-green-50 text-green-600 rounded-full flex items-center justify-center mx-auto mb-8 border border-green-100">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-14 w-14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <h2 class="text-4xl font-black text-slate-900 mb-2">Quiz Completed!</h2>
        <p class="text-slate-500 font-medium mb-10">Excellent effort! Your results are ready.</p>
        
        <div class="bg-slate-50 rounded-3xl p-10 mb-12 max-w-sm mx-auto border border-slate-100 shadow-sm">
          <p class="text-xs text-slate-400 uppercase font-black tracking-widest mb-2">Your Final Score</p>
          <div class="text-7xl font-black text-blue-600 mb-2 tracking-tighter">{{ result.score }} <span class="text-2xl text-slate-300">/ {{ result.total_points }}</span></div>
          <div class="text-2xl font-black text-slate-900">{{ result.percentage }}%</div>
        </div>

        <router-link to="/quizzes" class="btn-primary px-12 py-4 shadow-xl shadow-blue-600/20">Back to Quizzes</router-link>
      </div>
    </template>

    <template v-else-if="quiz">
      <header class="mb-12 bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
        <div class="flex flex-col md:flex-row md:justify-between md:items-end gap-6 mb-8">
          <div>
            <h1 class="text-4xl font-black text-slate-900 mb-2">{{ quiz.title }}</h1>
            <p class="text-slate-500 font-medium text-lg max-w-2xl">{{ quiz.description }}</p>
          </div>
          <div class="text-right shrink-0">
            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">Completion</p>
            <p class="text-3xl font-black text-blue-600">{{ progress }}%</p>
          </div>
        </div>
        <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
          <div class="bg-blue-600 h-full transition-all duration-700 ease-out" :style="{ width: `${progress}%` }"></div>
        </div>
      </header>

      <div class="space-y-10 mb-12">
        <div v-for="(question, index) in quiz.questions" :key="question.id" 
          class="card border-white bg-white shadow-lg transition-all duration-300"
          :class="answers[question.id] ? 'ring-2 ring-blue-500/10' : ''">
          <div class="flex justify-between items-start mb-8">
            <h3 class="text-2xl font-black text-slate-900 pr-4 leading-tight">
              <span class="text-blue-600 text-3xl mr-3">{{ index + 1 }}.</span>
              {{ question.question_text }}
            </h3>
            <span class="text-[10px] font-black text-slate-400 bg-slate-50 px-3 py-1.5 rounded-full border border-slate-100 whitespace-nowrap uppercase">{{ question.points }} pts</span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <button v-for="option in question.options" :key="option.id"
              @click="answers[question.id] = option.id"
              class="flex items-center p-5 rounded-2xl border-2 transition-all text-left group relative"
              :class="answers[question.id] === option.id 
                ? 'bg-blue-600 border-blue-600 text-white shadow-lg shadow-blue-600/20' 
                : 'bg-slate-50 border-slate-50 hover:border-slate-200 text-slate-600 hover:text-slate-900'">
              <div class="w-6 h-6 rounded-full border-2 mr-4 flex items-center justify-center shrink-0 transition-all"
                :class="answers[question.id] === option.id ? 'bg-white border-white' : 'border-slate-300 group-hover:border-slate-400'">
                <div v-if="answers[question.id] === option.id" class="w-2.5 h-2.5 bg-blue-600 rounded-full"></div>
              </div>
              <span class="font-bold text-lg leading-snug">{{ option.option_text }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Comment Space -->
      <div class="card mb-16 bg-blue-50/50 border-blue-100">
        <h3 class="text-xl font-black text-slate-900 mb-4 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
          </svg>
          Final Comments
        </h3>
        <p class="text-sm text-slate-500 mb-4 font-medium">Have any feedback or extra notes about this quiz? Your message will be sent to the instructor.</p>
        <textarea v-model="comment" rows="4" class="input bg-white border-slate-200 focus:ring-blue-500/20" placeholder="Write your comments here..."></textarea>
      </div>

      <div class="flex justify-center">
        <button @click="submitQuiz" :disabled="submitting" class="btn-primary px-20 py-5 text-2xl shadow-2xl shadow-blue-600/30 rounded-2xl">
          {{ submitting ? 'Submitting...' : 'Complete Assessment' }}
        </button>
      </div>
    </template>
  </div>
</template>
