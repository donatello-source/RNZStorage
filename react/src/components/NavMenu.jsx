// src/components/NavMenu.js
import React from 'react';
import { Box, Button } from '@mui/material';
import { Link } from 'react-router-dom';

const NavMenu = () => {
  return (
    <Box
      sx={{
        width: '200px',
        height: '100vh',
        backgroundColor: '#f0f0f0',
        padding: '20px',
      }}
    >
      <Button component={Link} to="/home" sx={{ display: 'block', marginBottom: '10px' }}>
        HOME
      </Button>
      <Button component={Link} to="/equipment" sx={{ display: 'block', marginBottom: '10px' }}>
        Sprzęt
      </Button>
      <Button component={Link} to="/quotes" sx={{ display: 'block', marginBottom: '10px' }}>
        Wyceny
      </Button>
      <Button component={Link} to="/create-quote" sx={{ display: 'block' }}>
        Utwórz Wycenę
      </Button>
    </Box>
  );
};

export default NavMenu;
