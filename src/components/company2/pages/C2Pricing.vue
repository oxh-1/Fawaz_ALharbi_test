<template>
  <Company2Layout page-title="Pricing Plans">
    <div class="c2-section-header">
      <div>
        <h2 class="c2-section-title">Merchant Subscription Tiers</h2>
        <p style="font-size:0.85rem;color:var(--c2-text-muted);margin-top:4px">
          SaaS subscription plans, feature gates, and recurring billing models.
        </p>
      </div>
      <button class="c2-btn c2-btn-primary" @click="openModal()">+ Create Plan</button>
    </div>

    <!-- Billing Toggle -->
    <div class="pricing-toggle-row">
      <span :class="{ active: !annual }">Billed Monthly</span>
      <label class="c2-switch">
        <input type="checkbox" v-model="annual" />
        <span class="c2-switch-slider"></span>
      </label>
      <span :class="{ active: annual }">
        Billed Annually <span class="save-badge">Save 20%</span>
      </span>
    </div>

    <!-- Plan Cards -->
    <div class="pricing-grid">
      <div v-for="plan in formattedPlans" :key="plan.id" :class="['pricing-card c2-card', { featured: plan.featured }]">
        <div class="plan-badge" v-if="plan.featured">⭐ Most Popular</div>
        <div class="plan-name">{{ plan.name }}</div>
        
        <div class="plan-price">
          <span class="plan-amount">SAR {{ annual ? plan.annual_price : plan.monthly_price }}</span>
          <span class="plan-period">/ {{ annual ? 'year' : 'month' }}</span>
        </div>
        
        <div class="plan-desc">{{ plan.description }}</div>
        
        <ul class="plan-features">
          <li v-for="(f, i) in plan.featureList" :key="i" :class="{ disabled: !f.included }">
            <span class="feature-icon">{{ f.included ? '✓' : '✕' }}</span>
            <span>{{ f.label }}</span>
          </li>
        </ul>
        
        <div style="display:flex;gap:8px;margin-top:auto">
          <button class="c2-btn c2-btn-ghost c2-btn-sm" @click="openModal(plan)" style="flex:1">
            Edit Plan
          </button>
          <button
            :class="['c2-btn c2-btn-sm', plan.active ? 'c2-btn-danger' : 'c2-btn-primary']"
            style="flex:1"
            @click="togglePlan(plan)">
            {{ plan.active ? 'Deactivate' : 'Activate' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="c2-modal-overlay" @click.self="showModal=false">
      <div class="c2-modal">
        <h3 class="c2-modal-title">{{ editing ? 'Edit Pricing Plan' : 'Create Pricing Plan' }}</h3>
        <button class="c2-modal-close" @click="showModal=false">✕</button>

        <div class="c2-form-group">
          <label class="c2-form-label">Plan Name *</label>
          <input v-model="form.name" class="c2-form-input" placeholder="e.g. Growth" />
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
          <div class="c2-form-group">
            <label class="c2-form-label">Monthly Price (SAR) *</label>
            <input v-model="form.monthly_price" type="number" class="c2-form-input" placeholder="99" />
          </div>
          <div class="c2-form-group">
            <label class="c2-form-label">Annual Price (SAR) *</label>
            <input v-model="form.annual_price" type="number" class="c2-form-input" placeholder="950" />
          </div>
        </div>

        <div class="c2-form-group">
          <label class="c2-form-label">Description</label>
          <input v-model="form.description" class="c2-form-input" placeholder="Plan target audience..." />
        </div>

        <div class="c2-form-group">
          <label class="c2-form-label">Features (comma-separated)</label>
          <textarea v-model="featuresInput" class="c2-form-input" rows="3" placeholder="Up to 10 merchants, Instant payouts, 24/7 Priority Support, Custom branding"></textarea>
        </div>

        <div style="display:flex;gap:20px;margin-top:10px">
          <label style="display:flex;align-items:center;gap:6px;font-size:0.85rem;cursor:pointer">
            <input type="checkbox" v-model="form.featured" />
            <span>Featured (Most Popular)</span>
          </label>
          <label style="display:flex;align-items:center;gap:6px;font-size:0.85rem;cursor:pointer">
            <input type="checkbox" v-model="form.active" />
            <span>Active for New Subscribers</span>
          </label>
        </div>

        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:24px">
          <button class="c2-btn c2-btn-ghost" @click="showModal=false">Cancel</button>
          <button class="c2-btn c2-btn-primary" @click="savePlan">{{ editing ? 'Update Plan' : 'Save Plan' }}</button>
        </div>
      </div>
    </div>
  </Company2Layout>
</template>

<script>
import Company2Layout from '../Company2Layout.vue';
import { pricingApi } from '@/services/api';

export default {
  name: 'C2Pricing',
  components: { Company2Layout },
  data() {
    return {
      annual: false,
      showModal: false,
      editing: null,
      featuresInput: '',
      form: {
        name: '',
        monthly_price: 99,
        annual_price: 950,
        description: '',
        featured: false,
        active: true,
        features: []
      },
      plans: []
    };
  },
  async mounted() {
    await this.fetchPlans();
  },
  computed: {
    formattedPlans() {
      return this.plans.map(p => {
        let fList = p.features;
        if (typeof fList === 'string') {
          try { fList = JSON.parse(fList); } catch { fList = []; }
        }
        if (Array.isArray(fList)) {
          fList = fList.map(item => {
            if (typeof item === 'string') return { label: item, included: true };
            return item;
          });
        } else {
          fList = [
            { label: 'Merchant Dashboard Access', included: true },
            { label: 'Booking Notifications', included: true },
            { label: 'Payment Gateway', included: true }
          ];
        }

        return {
          ...p,
          monthly_price: Number(p.monthly_price || p.monthlyPrice || 0),
          annual_price: Number(p.annual_price || p.annualPrice || 0),
          featured: Boolean(p.featured),
          active: Boolean(p.active !== false),
          featureList: fList
        };
      });
    }
  },
  methods: {
    async fetchPlans() {
      try {
        const res = await pricingApi.list();
        this.plans = Array.isArray(res) ? res : (res.data || []);
      } catch (e) {
        console.error('Failed to load pricing plans', e);
      }
    },
    openModal(p = null) {
      this.editing = p;
      if (p) {
        this.form = {
          name: p.name,
          monthly_price: p.monthly_price,
          annual_price: p.annual_price,
          description: p.description || '',
          featured: Boolean(p.featured),
          active: Boolean(p.active),
          features: p.featureList || []
        };
        this.featuresInput = (p.featureList || []).map(f => f.label || f).join(', ');
      } else {
        this.form = {
          name: '',
          monthly_price: 99,
          annual_price: 950,
          description: '',
          featured: false,
          active: true,
          features: []
        };
        this.featuresInput = 'Online Bookings, Merchant Portal, Email Support, Basic Analytics';
      }
      this.showModal = true;
    },
    async savePlan() {
      if (!this.form.name.trim()) {
        alert('Please enter a plan name.');
        return;
      }
      const rawFeatures = this.featuresInput.split(',').map(s => s.trim()).filter(Boolean);
      this.form.features = rawFeatures.map(label => ({ label, included: true }));
      try {
        if (this.editing) {
          await pricingApi.update(this.editing.id, this.form);
        } else {
          await pricingApi.create(this.form);
        }
        await this.fetchPlans();
        this.showModal = false;
      } catch (e) {
        console.error('Failed to save plan', e);
      }
    },
    async togglePlan(plan) {
      try {
        await pricingApi.toggle(plan.id);
        plan.active = !plan.active;
      } catch (e) {
        console.error('Failed to toggle plan', e);
      }
    }
  }
};
</script>

<style scoped>
.pricing-toggle-row {
  display: flex;
  align-items: center;
  gap: 14px;
  justify-content: center;
  margin-bottom: 28px;
  font-size: 0.95rem;
  font-weight: 600;
}
.pricing-toggle-row .active {
  color: var(--c2-accent);
}
.save-badge {
  background: var(--c2-success, #27ae60);
  color: white;
  font-size: 0.72rem;
  padding: 3px 8px;
  border-radius: 12px;
  margin-left: 6px;
}
.pricing-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 22px;
}
.pricing-card {
  display: flex;
  flex-direction: column;
  position: relative;
  padding: 28px 24px;
  border-radius: 14px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.pricing-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 30px rgba(0,0,0,0.09);
}
.pricing-card.featured {
  border: 2px solid var(--c2-accent);
  background: linear-gradient(180deg, rgba(0,170,255,0.03) 0%, rgba(0,0,0,0) 100%);
}
.plan-badge {
  position: absolute;
  top: -12px;
  left: 50%;
  transform: translateX(-50%);
  background: var(--c2-accent);
  color: white;
  font-size: 0.72rem;
  font-weight: 700;
  padding: 3px 14px;
  border-radius: 12px;
  white-space: nowrap;
  box-shadow: 0 2px 8px rgba(0,170,255,0.3);
}
.plan-name {
  font-size: 1.25rem;
  font-weight: 700;
  margin-bottom: 8px;
  color: var(--c2-text);
}
.plan-price {
  display: flex;
  align-items: baseline;
  gap: 4px;
  margin-bottom: 6px;
}
.plan-amount {
  font-size: 2.1rem;
  font-weight: 800;
  color: var(--c2-accent);
}
.plan-period {
  font-size: 0.85rem;
  color: var(--c2-text-muted);
}
.plan-desc {
  font-size: 0.82rem;
  color: var(--c2-text-muted);
  margin-bottom: 20px;
  min-height: 38px;
}
.plan-features {
  list-style: none;
  padding: 0;
  margin: 0 0 24px 0;
  display: flex;
  flex-direction: column;
  gap: 10px;
  flex: 1;
}
.plan-features li {
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  gap: 10px;
  color: var(--c2-text);
}
.feature-icon {
  font-weight: 800;
  color: #27ae60;
  font-size: 0.95rem;
}
.plan-features li.disabled {
  color: var(--c2-text-muted);
  text-decoration: line-through;
}
.plan-features li.disabled .feature-icon {
  color: #e74c3c;
}
</style>
