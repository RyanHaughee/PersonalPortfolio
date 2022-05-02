require('./bootstrap');
 
import { createApp } from 'vue'
import DraftIndex from './vue/draft/draft_index.vue'
import SchedulerIndex from './vue/scheduler/scheduler_index.vue'
import RecipeIndex from './vue/recipe/recipe_index.vue'

const draft_room = createApp({
    components: { DraftIndex}
})
  
draft_room.mount('#draft_app')

const scheduler = createApp({
  components: { SchedulerIndex }
})

scheduler.mount('#schedule_app')

const recipe_manager = createApp({
  components: { RecipeIndex }
})

recipe_manager.mount('#recipe_app')

