<template>
  <div class="error">
    <div class="container-floud">
      <div class="col-xs-12 ground-color text-center">
        <div class="container-error-404">
          <div class="clip"><div class="shadow"><span class="digit thirdDigit"></span></div></div>
          <div class="clip"><div class="shadow"><span class="digit secondDigit"></span></div></div>
          <div class="clip"><div class="shadow"><span class="digit firstDigit"></span></div></div>
          <div class="msg">OH!<span class="triangle"></span></div>
        </div>
        <h2 class="h1">Sorry! Page Not Found</h2>
        <p class="error-detail">The page or domain you are looking for does not exist.</p>
        <div class="error-info" v-if="badDomain || badRoute">
          <div v-if="badDomain" class="error-chip domain-chip">
            🌐 Unknown domain: <strong>{{ badDomain }}</strong>
          </div>
          <div v-if="badRoute" class="error-chip route-chip">
            🔗 Invalid route: <strong>{{ badRoute }}</strong>
          </div>
        </div>
        <div class="error-actions">
          <button @click="goHome" class="back-button">🏠 Go to Home</button>
          <button @click="goBack" class="back-button secondary-btn">← Go Back</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ErrorPage',
  data() {
    return {
      badDomain: '',
      badRoute: ''
    };
  },
  mounted() {
    // Detect invalid domain
    const validDomains = ['localhost', '127.0.0.1'];
    const host = window.location.hostname;
    if (!validDomains.some(d => host === d || host.endsWith('.' + d))) {
      this.badDomain = host;
    }

    // Detect invalid route
    const knownRoutes = [
      '/login', '/signup', '/dashboard', '/notification-settings', '/reports',
      '/profile', '/settings', '/invoices', '/fawaz', '/errorpage', '/404',
      '/c2/home', '/c2/merchant', '/c2/categories', '/c2/services', '/c2/booking',
      '/c2/reviews', '/c2/contact', '/c2/pricing', '/c2/ads', '/c2/content',
      '/c2/settlement', '/c2/reports', '/c2/permissions', '/c2/settings', '/c2'
    ];
    const currentPath = this.$route ? this.$route.path : window.location.pathname;
    if (!knownRoutes.includes(currentPath) && currentPath !== '/404') {
      this.badRoute = currentPath;
    }

    // Log for debugging
    if (this.badDomain) console.error(`[ErrorPage] Invalid domain: ${this.badDomain}`);
    if (this.badRoute) console.error(`[ErrorPage] Invalid route: ${this.badRoute}`);

    // Animated 404 digits
    this.randomNum = () => Math.floor(Math.random() * 9) + 1;
    let i = 0;
    const time = 30;
    const selector3 = document.querySelector('.thirdDigit');
    const selector2 = document.querySelector('.secondDigit');
    const selector1 = document.querySelector('.firstDigit');

    const loop3 = setInterval(() => { if (i > 40) { clearInterval(loop3); selector3.textContent = 4; } else { selector3.textContent = this.randomNum(); i++; } }, time);
    const loop2 = setInterval(() => { if (i > 80) { clearInterval(loop2); selector2.textContent = 0; } else { selector2.textContent = this.randomNum(); i++; } }, time);
    const loop1 = setInterval(() => { if (i > 100) { clearInterval(loop1); selector1.textContent = 4; } else { selector1.textContent = this.randomNum(); i++; } }, time);
  },
  methods: {
    goHome() {
      const user = localStorage.getItem('loggedInUser');
      this.$router.push(user ? '/dashboard' : '/login');
    },
    goBack() {
      if (window.history.length > 1) this.$router.go(-1);
      else this.goHome();
    }
  }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css?family=Anton|Passion+One|PT+Sans+Caption');

.error-detail {
  color: #7f8c8d;
  font-size: 1rem;
  margin: 8px 0 16px;
}

.error-info {
  display: flex;
  gap: 10px;
  justify-content: center;
  flex-wrap: wrap;
  margin: 12px 0 20px;
}

.error-chip {
  display: inline-block;
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-family: monospace;
}

.domain-chip {
  background: #fdecea;
  color: #c0392b;
  border: 1px solid #e74c3c;
}

.route-chip {
  background: #fff8e1;
  color: #f39c12;
  border: 1px solid #f39c12;
}

.error-actions {
  display: flex;
  gap: 12px;
  justify-content: center;
  margin-top: 16px;
}

.secondary-btn {
  background: transparent !important;
  color: #275559 !important;
  border: 2px solid #275559 !important;
}

.secondary-btn:hover {
  background: rgba(39,85,89,0.1) !important;
}

body {
  font-family: 'PT Sans Caption', sans-serif, 'arial', 'Times New Roman';
}

/* Error Page */
.error .clip .shadow {
  height: 180px;
}

.error .clip:nth-of-type(2) .shadow {
  width: 130px;
}

.error .clip:nth-of-type(1) .shadow,
.error .clip:nth-of-type(3) .shadow {
  width: 250px;
}

.error .digit {
  width: 150px;
  height: 150px;
  line-height: 150px;
  font-size: 120px;
  font-weight: bold;
}

.error h2 {
  font-size: 32px;
}

.error .msg {
  top: -190px;
  left: 30%;
  width: 80px;
  height: 80px;
  line-height: 80px;
  font-size: 32px;
}

.error span.triangle {
  top: 70%;
  right: 0%;
  border-left: 20px solid #535353;
  border-top: 15px solid transparent;
  border-bottom: 15px solid transparent;
}

.error .container-error-404 {
  margin-top: 10%;
  position: relative;
  height: 250px;
  padding-top: 40px;
}

.error .container-error-404 .clip {
  display: inline-block;
  transform: skew(-45deg);
}

.error .clip .shadow {
  overflow: hidden;
}

.error .clip:nth-of-type(2) .shadow {
  overflow: hidden;
  position: relative;
  box-shadow: inset 20px 0px 20px -15px rgba(150, 150, 150, 0.8), 20px 0px 20px -15px rgba(150, 150, 150, 0.8);
}

.error .clip:nth-of-type(3) .shadow:after,
.error .clip:nth-of-type(1) .shadow:after {
  content: "";
  position: absolute;
  right: -8px;
  bottom: 0px;
  z-index: 9999;
  height: 100%;
  width: 10px;
  background: linear-gradient(90deg, transparent, rgba(173, 173, 173, 0.8), transparent);
  border-radius: 50%;
}

.error .clip:nth-of-type(3) .shadow:after {
  left: -8px;
}

.error .digit {
  position: relative;
  top: 8%;
  color: white;
  background: #07B3F9;
  border-radius: 50%;
  display: inline-block;
  transform: skew(45deg);
}

.error .clip:nth-of-type(2) .digit {
  left: -10%;
}

.error .clip:nth-of-type(1) .digit {
  right: -20%;
}

.error .clip:nth-of-type(3) .digit {
  left: -20%;
}

.error h2 {
  color: #A2A2A2;
  font-weight: bold;
  padding-bottom: 20px;
}

.error .msg {
  position: relative;
  z-index: 9999;
  display: block;
  background: #535353;
  color: #A2A2A2;
  border-radius: 50%;
  font-style: italic;
}

.error .triangle {
  position: absolute;
  z-index: 999;
  transform: rotate(45deg);
  content: "";
  width: 0;
  height: 0;
}

/* Error Page */
@media (max-width: 767px) {
  .error .clip .shadow {
    height: 100px;
  }

  .error .clip:nth-of-type(2) .shadow {
    width: 80px;
  }

  .error .clip:nth-of-type(1) .shadow,
  .error .clip:nth-of-type(3) .shadow {
    width: 100px;
  }

  .error .digit {
    width: 80px;
    height: 80px;
    line-height: 80px;
    font-size: 52px;
  }

  .error h2 {
    font-size: 24px;
  }

  .error .msg {
    top: -110px;
    left: 15%;
    width: 40px;
    height: 40px;
    line-height: 40px;
    font-size: 18px;
  }

  .error span.triangle {
    top: 70%;
    right: -3%;
    border-left: 10px solid #535353;
    border-top: 8px solid transparent;
    border-bottom: 8px solid transparent;
  }

  .error .container-error-404 {
    height: 150px;
  }
}

/*--------------------------------------------Framework --------------------------------*/

.overlay {
  position: relative;
  z-index: 20;
}

.ground-color {
  background: white;
}

.item-bg-color {
  background: #EAEAEA;
}

/* Padding Section */
.padding-top {
  padding-top: 10px;
}

.padding-bottom {
  padding-bottom: 10px;
}

.padding-vertical {
  padding-top: 10px;
  padding-bottom: 10px;
}

.padding-horizontal {
  padding-left: 10px;
  padding-right: 10px;
}

.padding-all {
  padding: 10px;
}

.no-padding-left {
  padding-left: 0px;
}

.no-padding-right {
  padding-right: 0px;
}

.no-vertical-padding {
  padding-top: 0px;
  padding-bottom: 0px;
}

.no-horizontal-padding {
  padding-left: 0px;
  padding-right: 0px;
}

.no-padding {
  padding: 0px;
}

/* Margin section */
.margin-top {
  margin-top: 10px;
}

.margin-bottom {
  margin-bottom: 10px;
}

.margin-right {
  margin-right: 10px;
}

.margin-left {
  margin-left: 10px;
}

.margin-horizontal {
  margin-left: 10px;
  margin-right: 10px;
}

.margin-vertical {
  margin-top: 10px;
  margin-bottom: 10px;
}

.margin-all {
  margin: 10px;
}

.no-margin {
  margin: 0px;
}

.no-vertical-margin {
  margin-top: 0px;
  margin-bottom: 0px;
}

.no-horizontal-margin {
  margin-left: 0px;
  margin-right: 0px;
}

.inside-col-shrink {
  margin: 0px 20px;
}

hr {
  margin: 0px;
  padding: 0px;
  border-top: 1px dashed #999;
}

.back-button {
  margin-top: 20px;
  padding: 10px 20px;
  font-size: 16px;
  background-color: #07B3F9;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

.back-button:hover {
  background-color: #005f9e;
}
</style>
