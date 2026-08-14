export const bookingValidators = {
  // Validate reschedule date
  validateRescheduleDate(newDate, currentBooking) {
    const now = new Date();
    const bookingDate = new Date(currentBooking.scheduled_at);
    
    // Must be in future
    if (newDate <= now) {
      return { valid: false, error: 'Date must be in the future' };
    }
    
    // Must be at least 24 hours from now (for most services)
    const minDate = new Date(now.getTime() + 24 * 60 * 60 * 1000);
    if (newDate < minDate) {
      return { valid: false, error: 'Must reschedule at least 24 hours in advance' };
    }
    
    // Cannot reschedule more than 5 times
    if (currentBooking.reschedule_count >= 5) {
      return { valid: false, error: 'Maximum reschedules reached' };
    }
    
    return { valid: true };
  },

  // Validate review
  validateReview(review) {
    if (!review.rating || review.rating < 1 || review.rating > 5) {
      return { valid: false, error: 'Please provide a rating' };
    }
    
    if (review.comment && review.comment.length > 1000) {
      return { valid: false, error: 'Review must be less than 1000 characters' };
    }
    
    return { valid: true };
  },

  // Validate cancellation reason
  validateCancellation(reason) {
    const validReasons = [
      'schedule_conflict',
      'not_feeling_well',
      'emergency',
      'found_alternative',
      'other'
    ];
    
    if (!validReasons.includes(reason)) {
      return { valid: false, error: 'Invalid cancellation reason' };
    }
    
    return { valid: true };
  }
};
