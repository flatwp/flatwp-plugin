import { Switch, Route, Redirect, useHistory } from 'react-router-dom';
import { Toaster } from 'sonner';
import { Layout } from './components/Layout';
import { Dashboard } from './pages/Dashboard';
import { CacheRevalidation } from './pages/CacheRevalidation';
import { Settings } from './pages/Settings';
import { MenuSettings } from './pages/MenuSettings';
import { ActivityLogPage } from './pages/ActivityLog';
import { SetupWizard } from './pages/SetupWizard';
import { api } from './lib/api';
import { useEffect } from 'react';

function AppRoutes() {
  const history = useHistory();

  useEffect(() => {
    // Fetch wizard status and redirect if needed
    api.getWizardStatus().then((response: any) => {
      const showWizard = response?.data?.show_wizard;

      if (showWizard && window.location.hash !== '#/wizard') {
        history.push('/wizard');
      }
    });
  }, [history]);

  return (
    <Switch>
      <Route path="/wizard" component={SetupWizard} />
      <Route exact path="/">
        <Layout>
          <Dashboard />
        </Layout>
      </Route>
      <Route path="/cache-revalidation">
        <Layout>
          <CacheRevalidation />
        </Layout>
      </Route>
      {/* Legacy routes - redirect to new combined page */}
      <Route path="/cache">
        <Redirect to="/cache-revalidation" />
      </Route>
      <Route path="/revalidation">
        <Redirect to="/cache-revalidation" />
      </Route>
      <Route path="/settings">
        <Layout>
          <Settings />
        </Layout>
      </Route>
      <Route path="/menus">
        <Layout>
          <MenuSettings />
        </Layout>
      </Route>
      <Route path="/activity-log">
        <Layout>
          <ActivityLogPage />
        </Layout>
      </Route>
      <Route>
        <Redirect to="/" />
      </Route>
    </Switch>
  );
}

function App() {
  return (
    <>
      <AppRoutes />
      <Toaster richColors position="top-right" />
    </>
  );
}

export default App;
