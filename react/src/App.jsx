// src/App.jsx
import React from 'react';
import { BrowserRouter as Router, Route, Routes } from 'react-router-dom';
import LoginForm from './components/LoginForm.jsx';
import HomePage from './components/HomePage.jsx';
import AdminPanel from './components/AdminPanel.jsx';
import EquipmentPage from './components/EquipmentPage.jsx';
import QuotesFilesPage from './components/QuotesFilesPage.jsx';
import QuotesPage from './components/QuotesPage.jsx';
import CreateQuotePage from './components/CreateQuotePage';


function App() {
  return (
    <Router>
      <Routes>
        <Route path="/" element={<LoginForm />} />
        <Route path="/home" element={<HomePage />} />
        <Route path="/admin" element={<AdminPanel />} />
        <Route path="/equipment" element={<EquipmentPage />} />
        <Route path="/quotes" element={<QuotesPage />} />
        <Route path="/quotes-files" element={<QuotesFilesPage />} />
        <Route path="/create-quote" element={<CreateQuotePage/>} />
      </Routes>
    </Router>
  );
}

export default App;
