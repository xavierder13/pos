// src/main.jsx

import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import './index.css'
import App from './App.jsx'
import { applyCssVars } from './utils/theme'

// the palette in utils/theme.js → --accent, --surface … for the CSS
applyCssVars()

createRoot(document.getElementById('root')).render(
  <StrictMode>
    <App />
  </StrictMode>
)
