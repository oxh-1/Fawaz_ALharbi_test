/**
 * AI Handlers for Cloudflare Workers API
 * 
 * Endpoints:
 * - POST /api/ai/booking-assistant
 * - POST /api/ai/stock-prediction
 * - POST /api/ai/real-estate-analyzer
 * - POST /api/ai/dev-tutor
 * - POST /api/ai/chat
 * - GET  /api/chat
 * - POST /api/chat
 */

import { jsonResponse } from '../lib/cors.js';
import { executeAI } from '../lib/ai.js';

// In-memory / fast session history buffer for live chats
const CHAT_HISTORY = [
  {
    id: 1,
    sender_id: 9999,
    message: "👋 Welcome to the Fawaz Unified Platform! I'm your multi-assistant AI powered by Cloudflare AI & OpenAI. Ask me about booking appointments, stock market predictions, real estate Sukuk yields, or developer coding tutorials!",
    created_at: new Date(Date.now() - 3600000).toISOString()
  }
];

/**
 * Generic AI Chat Handler
 * POST /api/ai/chat
 */
export async function handleAIChat(request, env, authUser) {
  let body;
  try {
    body = await request.json();
  } catch {
    return jsonResponse({ success: false, message: 'Invalid JSON body.' }, 400, request);
  }

  const { message, prompt, assistant = 'general', messages = [] } = body;
  const userPrompt = message || prompt;

  if (!userPrompt && (!messages || !messages.length)) {
    return jsonResponse({ success: false, message: 'Prompt or message is required.' }, 422, request);
  }

  const aiResult = await executeAI({
    assistant,
    prompt: userPrompt,
    messages,
    env
  });

  return jsonResponse({
    success: true,
    assistant,
    provider: aiResult.provider,
    model: aiResult.model,
    response: aiResult.text,
    message: aiResult.text,
    created_at: new Date().toISOString()
  }, 200, request);
}

/**
 * 1. AI Booking Assistant
 * POST /api/ai/booking-assistant
 */
export async function handleBookingAssistant(request, env, authUser) {
  let body = {};
  try { body = await request.json(); } catch {}

  const { message, prompt, messages = [] } = body;
  const userPrompt = message || prompt || 'Tell me about available merchant services and how to book an appointment.';

  const aiResult = await executeAI({
    assistant: 'booking',
    prompt: userPrompt,
    messages,
    env
  });

  return jsonResponse({
    success: true,
    type: 'ai_booking_assistant',
    provider: aiResult.provider,
    model: aiResult.model,
    response: aiResult.text,
    reply: aiResult.text,
    created_at: new Date().toISOString()
  }, 200, request);
}

/**
 * 2. AI Stock Prediction (Non-Financial Advice)
 * POST /api/ai/stock-prediction
 */
export async function handleStockPrediction(request, env, authUser) {
  let body = {};
  try { body = await request.json(); } catch {}

  const { ticker, query, message, prompt, messages = [] } = body;
  const targetTicker = ticker ? `for ticker ${ticker}` : '';
  const userPrompt = message || prompt || query || `Analyze the market momentum, unusual volume anomalies, and predicted buy support levels ${targetTicker}.`;

  const aiResult = await executeAI({
    assistant: 'stock',
    prompt: userPrompt,
    messages,
    env
  });

  return jsonResponse({
    success: true,
    type: 'ai_stock_prediction',
    is_financial_advice: false,
    disclaimer: '⚠️ AI-generated analysis for educational and quantitative screening purposes only. Strictly non-financial advice.',
    ticker: ticker || 'MARKET_OVERVIEW',
    provider: aiResult.provider,
    model: aiResult.model,
    response: aiResult.text,
    prediction: aiResult.text,
    created_at: new Date().toISOString()
  }, 200, request);
}

/**
 * 3. AI Real Estate Analyzer
 * POST /api/ai/real-estate-analyzer
 */
export async function handleRealEstateAnalyzer(request, env, authUser) {
  let body = {};
  try { body = await request.json(); } catch {}

  const { property_id, property_name, query, message, prompt, messages = [] } = body;
  const propRef = property_name || (property_id ? `Property #${property_id}` : 'commercial Grade-A portfolio');
  const userPrompt = message || prompt || query || `Evaluate the rental dividend yield, 5-year projected IRR, tokenized Sukuk structure, and capital growth for ${propRef}.`;

  const aiResult = await executeAI({
    assistant: 'real_estate',
    prompt: userPrompt,
    messages,
    env
  });

  return jsonResponse({
    success: true,
    type: 'ai_real_estate_analyzer',
    property: propRef,
    target_annual_yield: '11.8%',
    provider: aiResult.provider,
    model: aiResult.model,
    response: aiResult.text,
    analysis: aiResult.text,
    created_at: new Date().toISOString()
  }, 200, request);
}

/**
 * 4. AI Developer Tutor
 * POST /api/ai/dev-tutor
 */
export async function handleDevTutor(request, env, authUser) {
  let body = {};
  try { body = await request.json(); } catch {}

  const { topic, code, question, message, prompt, messages = [] } = body;
  let userPrompt = message || prompt || question;
  if (!userPrompt) {
    if (code) {
      userPrompt = `Please review, explain, and debug this code:\n\n\`\`\`\n${code}\n\`\`\``;
    } else if (topic) {
      userPrompt = `Please give me a complete interactive lesson on ${topic} with step-by-step instructions and runnable code blocks.`;
    } else {
      userPrompt = 'What are the top full-stack web development and AI engineering tracks available in Company 5 Academy?';
    }
  }

  const aiResult = await executeAI({
    assistant: 'dev_tutor',
    prompt: userPrompt,
    messages,
    env
  });

  return jsonResponse({
    success: true,
    type: 'ai_developer_tutor',
    topic: topic || 'General Programming',
    provider: aiResult.provider,
    model: aiResult.model,
    response: aiResult.text,
    tutor_reply: aiResult.text,
    created_at: new Date().toISOString()
  }, 200, request);
}

/**
 * Chat Widget list & send
 * GET /api/chat & POST /api/chat
 */
export async function handleChatList(request, env, authUser) {
  return jsonResponse(CHAT_HISTORY, 200, request);
}

export async function handleChatSend(request, env, authUser) {
  let body;
  try {
    body = await request.json();
  } catch {
    return jsonResponse({ success: false, message: 'Invalid JSON body.' }, 400, request);
  }

  const { message, assistant = 'general' } = body;
  if (!message || !message.trim()) {
    return jsonResponse({ success: false, message: 'Message is required.' }, 422, request);
  }

  const userMsg = {
    id: Date.now(),
    sender_id: authUser?.sub || 1,
    message: message.trim(),
    created_at: new Date().toISOString()
  };
  CHAT_HISTORY.push(userMsg);

  // Determine the best assistant persona automatically if general
  let targetAssistant = assistant;
  if (targetAssistant === 'general') {
    const textLower = message.toLowerCase();
    if (textLower.includes('stock') || textLower.includes('buy') || textLower.includes('tasi') || textLower.includes('سهم') || textLower.includes('أسهم')) {
      targetAssistant = 'stock';
    } else if (textLower.includes('estate') || textLower.includes('sukuk') || textLower.includes('rent') || textLower.includes('عقار') || textLower.includes('صكوك')) {
      targetAssistant = 'real_estate';
    } else if (textLower.includes('code') || textLower.includes('python') || textLower.includes('vue') || textLower.includes('debug') || textLower.includes('كود') || textLower.includes('برمجة')) {
      targetAssistant = 'dev_tutor';
    } else if (textLower.includes('book') || textLower.includes('salon') || textLower.includes('service') || textLower.includes('حجز') || textLower.includes('موعد')) {
      targetAssistant = 'booking';
    }
  }

  const aiResult = await executeAI({
    assistant: targetAssistant,
    prompt: message.trim(),
    messages: CHAT_HISTORY.slice(-6),
    env
  });

  const aiMsg = {
    id: Date.now() + 1,
    sender_id: 9999,
    message: aiResult.text,
    assistant: targetAssistant,
    provider: aiResult.provider,
    model: aiResult.model,
    created_at: new Date().toISOString()
  };
  CHAT_HISTORY.push(aiMsg);

  // Keep chat history buffer under 50 items
  if (CHAT_HISTORY.length > 50) {
    CHAT_HISTORY.splice(0, CHAT_HISTORY.length - 50);
  }

  return jsonResponse({
    success: true,
    user_message: userMsg,
    ai_message: aiMsg,
    provider: aiResult.provider,
    model: aiResult.model
  }, 200, request);
}
