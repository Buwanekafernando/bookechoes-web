import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import Home from './pages/Home';
import Footer from './components/Footer';
import AdminLogin from './pages/admin/AdminLogin';
import AdminDashboard from './pages/admin/AdminDashboard';
import AdminAuthors from './pages/admin/AdminAuthors';
import AdminBooks from './pages/admin/AdminBooks';
import AdminEbooks from './pages/admin/AdminEbooks';
import AdminBookshops from './pages/admin/AdminBookshops';
import './App.css';

function App() {
  return (
    <Router>
      <div className="App">
        <Routes>
          <Route path="/" element={<Home />} />

          {/* Admin Routes */}
          <Route path="/admin" element={<AdminLogin />} />
          <Route path="/admin/login" element={<AdminLogin />} />
          <Route path="/admin/dashboard" element={<AdminDashboard />} />
          <Route path="/admin/authors" element={<AdminAuthors />} />
          <Route path="/admin/books" element={<AdminBooks />} />
          <Route path="/admin/ebooks" element={<AdminEbooks />} />
          <Route path="/admin/bookshops" element={<AdminBookshops />} />

        </Routes>
        <Footer />
      </div>
    </Router>
  );
}

export default App;
