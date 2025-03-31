import React from 'react';
import { Button, TextField } from '@mui/material';
import { useNavigate } from 'react-router-dom';

function LoginForm() {
  const navigate = useNavigate();

  const handleSubmit = (e) => {
    e.preventDefault();
    // Możesz dodać tutaj logikę logowania
    navigate('/home');
  };

  return (
    <div style={{ maxWidth: 400, margin: 'auto', paddingTop: 100 }}>
      <form onSubmit={handleSubmit}>
        <TextField
          label="Email"
          fullWidth
          margin="normal"
          variant="outlined"
          type="email"
        />
        <TextField
          label="Hasło"
          fullWidth
          margin="normal"
          variant="outlined"
          type="password"
        />
        <Button
          type="submit"
          fullWidth
          variant="contained"
          color="primary"
          style={{ marginTop: 20 }}
        >
          Zaloguj
        </Button>
      </form>
    </div>
  );
}

export default LoginForm;
