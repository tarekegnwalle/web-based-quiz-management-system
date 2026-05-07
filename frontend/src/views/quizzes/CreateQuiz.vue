<script setup>
import { ref, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../api/axios'

const router = useRouter()
const loading = ref(false)
const selectedFile = ref(null)
const courses = ref([])

const quiz = ref({
  course_id: '',
  description: '',
  year: 1,
  questions: [
    {
      question_text: '',
      points: 1,
      options: [
        { option_text: '', is_correct: true },
        { option_text: '', is_correct: false },
        { option_text: '', is_correct: false },
        { option_text: '', is_correct: false }
      ]
    }
  ]
})

const fetchCourses = async () => {
  try {
    const { data } = await api.get(`/courses?year=${quiz.value.year}`)
    courses.value = data
    if (data.length > 0) {
      quiz.value.course_id = data[0].id
    } else {
      quiz.value.course_id = ''
    }
  } catch (err) {
    console.error('Failed to fetch courses', err)
  }
}

// Re-fetch courses whenever the year changes
watch(() => quiz.value.year, fetchCourses)

onMounted(fetchCourses)

const handleFileChange = (e) => {
  selectedFile.value = e.target.files[0]
}

const addQuestion = () => {
  quiz.value.questions.push({
    question_text: '',
    points: 1,
    options: [
      { option_text: '', is_correct: true },
      { option_text: '', is_correct: false },
      { option_text: '', is_correct: false },
      { option_text: '', is_correct: false }
    ]
  })
}

const removeQuestion = (index) => {
  quiz.value.questions.splice(index, 1)
}

const setCorrect = (qIndex, oIndex) => {
  quiz.value.questions[qIndex].options.forEach((opt, idx) => {
    opt.is_correct = idx === oIndex
  })
}

const handleSubmit = async () => {
  if (!quiz.value.course_id) {
    alert('Please select a course.')
    return
  }
  loading.value = true
  try {
    const formData = new FormData()
    formData.append('course_id', quiz.value.course_id)
    formData.append('description', quiz.value.description || '')
    formData.append('year', quiz.value.year)
    
    if (selectedFile.value) {
      formData.append('document', selectedFile.value)
    }

    quiz.value.questions.forEach((q, qIdx) => {
      formData.append(`questions[${qIdx}][question_text]`, q.question_text)
      formData.append(`questions[${qIdx}][points]`, q.points)
      q.options.forEach((o, oIdx) => {
        formData.append(`questions[${qIdx}][options][${oIdx}][option_text]`, o.option_text)
        formData.append(`questions[${qIdx}][options][${oIdx}][is_correct]`, o.is_correct ? 1 : 0)
      })
    })

    await api.post('/quizzes', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    
    router.push('/quizzes')
  } catch (err) {
    alert('Failed to create quiz: ' + (err.response?.data?.message || 'Check your inputs'))
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="max-w-4xl mx-auto">
    <div class="flex items-center space-x-4 mb-10">
      <router-link to="/quizzes" class="p-2 bg-slate-100 rounded-lg hover:bg-slate-200 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </router-link>
      <h1 class="text-4xl font-black text-slate-900">Publish Assessment</h1>
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-8">
      <div class="card bg-white border-slate-100">
        <h3 class="text-xl font-bold text-slate-900 mb-6">General Information</h3>
        <div class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-bold text-slate-700 mb-2">Target Academic Year</label>
              <select v-model="quiz.year" class="input text-lg">
                <option :value="1">1st Year</option>
                <option :value="2">2nd Year</option>
                <option :value="3">3rd Year</option>
                <option :value="4">4th Year</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-bold text-slate-700 mb-2">Select Course</label>
              <select v-model="quiz.course_id" required class="input text-lg" :disabled="courses.length === 0">
                <option v-if="courses.length === 0" value="">No courses found for this year</option>
                <option v-for="c in courses" :key="c.id" :value="c.id">{{ c.name }} ({{ c.code }})</option>
              </select>
            </div>
          </div>
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Description</label>
            <textarea v-model="quiz.description" rows="2" class="input" placeholder="Quick summary of the quiz content..."></textarea>
          </div>
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Attach Document (PDF/Word)</label>
            <input type="file" @change="handleFileChange" accept=".pdf,.doc,.docx" class="input file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            <p class="text-[10px] text-slate-400 mt-1 uppercase font-bold tracking-widest italic">Max size: 10MB</p>
          </div>
        </div>
      </div>

      <div v-for="(question, qIndex) in quiz.questions" :key="qIndex" class="card relative overflow-visible border-slate-100 bg-white shadow-lg">
        <div class="absolute -left-3 top-6 w-10 h-10 bg-blue-600 rounded-2xl flex items-center justify-center font-black text-white shadow-xl shadow-blue-600/20">
          {{ qIndex + 1 }}
        </div>
        
        <div class="flex justify-between items-start mb-8 ml-8">
          <div class="flex-grow mr-6">
            <label class="block text-sm font-bold text-slate-700 mb-2">Question Text</label>
            <input v-model="question.question_text" type="text" required class="input" placeholder="Type your question here...">
          </div>
          <div class="w-24">
            <label class="block text-sm font-bold text-slate-700 mb-2">Points</label>
            <input v-model="question.points" type="number" min="1" class="input text-center font-bold">
          </div>
          <button @click="removeQuestion(qIndex)" v-if="quiz.questions.length > 1" type="button" class="ml-6 mt-8 text-slate-300 hover:text-red-500 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
        </div>

        <div class="space-y-4 ml-8">
          <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Options (Exactly 4)</label>
          <div v-for="(option, oIndex) in question.options" :key="oIndex" class="flex items-center space-x-4">
            <button @click="setCorrect(qIndex, oIndex)" type="button" 
              class="w-7 h-7 rounded-full border-2 flex items-center justify-center transition-all shrink-0"
              :class="option.is_correct ? 'bg-green-500 border-green-500 text-white' : 'border-slate-200 hover:border-slate-300 bg-slate-50'">
              <svg v-if="option.is_correct" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
            </button>
            <input v-model="option.option_text" type="text" required class="input py-2 bg-slate-50 border-slate-100" :placeholder="`Option ${oIndex + 1}`">
          </div>
        </div>
      </div>

      <div class="flex justify-between items-center pt-8">
        <button @click="addQuestion" type="button" class="btn-secondary flex items-center bg-white border-slate-200">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Add Question
        </button>
        <button type="submit" :disabled="loading || courses.length === 0" class="btn-primary px-12 py-4 text-xl">
          {{ loading ? 'Publishing...' : 'Publish Quiz' }}
        </button>
      </div>
    </form>
  </div>
</template>
