import React from 'react';
import ReactDOM from 'react-dom/client';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { HashRouter } from 'react-router-dom';
import App from './App';
import './styles/globals.css';

// Debug: Check if WordPress globals are available
if (!window.flatwpReactAdmin) {
  console.error('❌ FlatWP: WordPress globals not found. Plugin may not be enqueuing scripts correctly.');
  document.getElementById('flatwp-root')!.innerHTML =
    '<div style="padding: 20px; background: #fee; border: 1px solid #c33; border-radius: 4px; margin: 20px;">' +
    '<h2 style="color: #c33; margin-top: 0;">⚠️ FlatWP React Admin Error</h2>' +
    '<p><strong>WordPress globals not found.</strong></p>' +
    '<p>The plugin scripts may not be loading correctly. Please check:</p>' +
    '<ul>' +
    '<li>Plugin is activated in WordPress</li>' +
    '<li>You\'re viewing the correct admin page (FlatWP menu)</li>' +
    '<li>No JavaScript errors in browser console (F12)</li>' +
    '<li>Plugin files are in <code>wp-content/plugins/react-plugin/</code></li>' +
    '</ul>' +
    '</div>';
} else {
  // Create a client
  const queryClient = new QueryClient({
    defaultOptions: {
      queries: {
        refetchOnWindowFocus: false,
        retry: 1,
        staleTime: 30000,
      },
    },
  });

  const rootElement = document.getElementById('flatwp-root');
  if (rootElement) {
    ReactDOM.createRoot(rootElement).render(
      <React.StrictMode>
        <QueryClientProvider client={queryClient}>
          <HashRouter>
            <App />
          </HashRouter>
        </QueryClientProvider>
      </React.StrictMode>
    );
  } else {
    console.error('❌ FlatWP: Root element not found');
  }
}
