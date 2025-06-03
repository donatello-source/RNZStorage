// src/components/Header.jsx
import React, { useEffect, useState } from 'react';
import { AppBar, Toolbar, Typography, Box, Button } from '@mui/material';
import { useNavigate } from 'react-router-dom';

const Header = () => {
  const navigate = useNavigate();
  
  const handleLogout = async () => {
  localStorage.removeItem('user');
  document.cookie = 'PHPSESSID=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
  navigate('/');
  };
  useEffect(() => {
    const storedUser = JSON.parse(localStorage.getItem('user'));

    if (!storedUser) {
      navigate('/');
    } else if (storedUser.role.includes('ROLE_ADMIN')) {
      navigate('/admin');
    }
  }, [navigate]);
  return (
    <AppBar position="static" sx={{ backgroundColor: 'black' }}>
      <Toolbar>
        <Box sx={{ flexGrow: 1 }}>
          <Typography variant="h6">Robimy na Żywo</Typography>
        </Box>
        <Button className="logout-button" variant="contained" onClick={handleLogout}>
          Wyloguj się
        </Button>
      </Toolbar>
    </AppBar>
  );
};

export default Header;
