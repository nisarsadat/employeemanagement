import { defineStore } from 'pinia'

// ✅ Named export
export const useCalendarStore = defineStore('calendar', {
  state: () => ({
    calendar: localStorage.getItem('apartment-calendar') || 'gregorian',
  }),
  getters: {
    calendarLabel: (state) => {
      switch (state.calendar) {
        case 'gregorian': return 'Gregorian'
        case 'jalali':    return 'هجری شمسی'
        case 'hijri':     return 'هجری قمری'
        default:          return 'Calendar'
      }
    },
  },
  actions: {
    setCalendar(c) {
      this.calendar = c
      localStorage.setItem('apartment-calendar', c)
    },
    cycle() {
      const order = ['gregorian', 'jalali', 'hijri']
      const idx = order.indexOf(this.calendar)
      const next = order[(idx + 1) % order.length]
      this.setCalendar(next)
    },
  },
})
