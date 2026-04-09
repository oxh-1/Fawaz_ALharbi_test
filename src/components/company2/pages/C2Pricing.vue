<template>
  <Company2Layout page-title="Pricing">
    <!-- Billing Toggle -->
    <div class="pricing-toggle-row">
      <span :class="{ active: !annual }">Monthly</span>
      <label class="c2-switch"><input type="checkbox" v-model="annual" /><span class="c2-switch-slider"></span></label>
      <span :class="{ active: annual }">Annual <span class="save-badge">Save 20%</span></span>
    </div>

    <!-- Plan Cards -->
    <div class="pricing-grid">
      <div v-for="plan in plans" :key="plan.id" :class="['pricing-card c2-card', { featured: plan.featured }]">
        <div class="plan-badge" v-if="plan.featured">⭐ Most Popular</div>
        <div class="plan-name">{{ plan.name }}</div>
        <div class="plan-price">
          <span class="plan-amount">SAR {{ annual ? plan.annualPrice : plan.monthlyPrice }}</span>
          <span class="plan-period">/{{ annual ? 'yr' : 'mo' }}</span>
        </div>
        <div class="plan-desc">{{ plan.description }}</div>
        <ul class="plan-features">
          <li v-for="f in plan.features" :key="f.label" :class="{ disabled: !f.included }">
            <span>{{ f.included ? '✓' : '✗' }}</span> {{ f.label }}
          </li>
        </ul>
        <button
          :class="['c2-btn', plan.active ? 'c2-btn-danger' : 'c2-btn-primary']"
          style="width:100%;margin-top:auto"
          @click="plan.active = !plan.active">
          {{ plan.active ? 'Deactivate' : 'Activate Plan' }}
        </button>
      </div>
    </div>
  </Company2Layout>
</template>

<script>
import Company2Layout from '../Company2Layout.vue';

export default {
  name: 'C2Pricing',
  components: { Company2Layout },
  data() {
    return {
      annual: false,
      plans: [
        {
          id: 1, name: 'Basic', monthlyPrice: 49, annualPrice: 470, featured: false, active: true,
          description: 'Perfect for small businesses just starting out.',
          features: [
            { label: 'Up to 5 merchants', included: true },
            { label: '100 bookings/month', included: true },
            { label: 'Basic analytics', included: true },
            { label: 'Email support', included: true },
            { label: 'Custom domain', included: false },
            { label: 'API access', included: false }
          ]
        },
        {
          id: 2, name: 'Pro', monthlyPrice: 149, annualPrice: 1430, featured: true, active: true,
          description: 'For growing businesses with more needs.',
          features: [
            { label: 'Up to 50 merchants', included: true },
            { label: 'Unlimited bookings', included: true },
            { label: 'Advanced analytics', included: true },
            { label: 'Priority support', included: true },
            { label: 'Custom domain', included: true },
            { label: 'API access', included: false }
          ]
        },
        {
          id: 3, name: 'Enterprise', monthlyPrice: 399, annualPrice: 3830, featured: false, active: false,
          description: 'Full-featured for large operations.',
          features: [
            { label: 'Unlimited merchants', included: true },
            { label: 'Unlimited bookings', included: true },
            { label: 'Full analytics suite', included: true },
            { label: '24/7 dedicated support', included: true },
            { label: 'Custom domain', included: true },
            { label: 'API access', included: true }
          ]
        }
      ]
    };
  }
};
</script>

<style scoped>
.pricing-toggle-row { display: flex; align-items: center; gap: 12px; justify-content: center; margin-bottom: 28px; font-size: 0.95rem; font-weight: 600; }
.pricing-toggle-row .active { color: #00aaff; }
.save-badge { background: #27ae60; color: white; font-size: 0.7rem; padding: 2px 7px; border-radius: 12px; margin-left: 6px; }
.pricing-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
.pricing-card { display: flex; flex-direction: column; position: relative; padding: 28px 24px; }
.pricing-card.featured { border: 2px solid #00aaff; }
.plan-badge { position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: #00aaff; color: white; font-size: 0.72rem; font-weight: 700; padding: 3px 12px; border-radius: 12px; white-space: nowrap; }
.plan-name { font-size: 1.2rem; font-weight: 700; margin-bottom: 8px; }
.plan-price { display: flex; align-items: baseline; gap: 4px; margin-bottom: 6px; }
.plan-amount { font-size: 2rem; font-weight: 800; color: #00aaff; }
.plan-period { font-size: 0.85rem; color: var(--c2-text-muted); }
.plan-desc { font-size: 0.82rem; color: var(--c2-text-muted); margin-bottom: 18px; }
.plan-features { list-style: none; padding: 0; margin: 0 0 20px 0; display: flex; flex-direction: column; gap: 8px; flex: 1; }
.plan-features li { font-size: 0.875rem; display: flex; align-items: center; gap: 8px; }
.plan-features li.disabled { color: var(--c2-text-muted); text-decoration: line-through; }
.plan-features li span { font-weight: 700; color: #27ae60; }
.plan-features li.disabled span { color: #e74c3c; }
@media (max-width: 900px) { .pricing-grid { grid-template-columns: 1fr; } }
</style>
