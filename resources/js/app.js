require('./bootstrap');
 
import { createApp } from 'vue'
import DraftIndex from './vue/draft/draft_index.vue'
import SchedulerIndex from './vue/scheduler/scheduler_index.vue'

const draft_room = createApp({
    components: { DraftIndex, SchedulerIndex }
})
  
draft_room.mount('#app')