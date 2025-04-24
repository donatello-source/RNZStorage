// src/components/Header.jsx
import React from 'react';
import { AppBar, Toolbar, Typography, Box } from '@mui/material';

const Header = () => {
  return (
    <AppBar position="static" sx={{ backgroundColor: 'black' }}>
      <Toolbar>
        <Box sx={{ flexGrow: 1 }}>
          <Typography variant="h6">Robimy na Żywo</Typography>
        </Box>
      </Toolbar>
    </AppBar>
  );
};

export default Header;
