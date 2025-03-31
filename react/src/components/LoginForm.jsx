import React, { useState } from 'react';
import { Button, TextField } from '@mui/material';
import { useNavigate } from 'react-router-dom';
import '../style/LoginForm.css';

function LoginForm() {
  const navigate = useNavigate();
  const [isRegistering, setIsRegistering] = useState(false);
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [firstName, setFirstName] = useState('');
  const [lastName, setLastName] = useState('');
  const [errors, setErrors] = useState({});

  const handleSubmit = async (e) => {
    e.preventDefault();
    let newErrors = {};

    if (isRegistering) {
      if (!firstName) newErrors.firstName = 'Imię jest wymagane';
      if (!lastName) newErrors.lastName = 'Nazwisko jest wymagane';
      if (!email) newErrors.email = 'Email jest wymagany';
      if (!password) newErrors.password = 'Hasło jest wymagane';
      if (password !== confirmPassword) newErrors.confirmPassword = 'Hasła muszą być identyczne';
    } else {
      if (!email) newErrors.email = 'Email jest wymagany';
      if (!password) newErrors.password = 'Hasło jest wymagane';
    }

    setErrors(newErrors);

    if (Object.keys(newErrors).length === 0) {
      if (isRegistering) {
        const response = await fetch('/api/person', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            imie: firstName,
            nazwisko: lastName,
            mail: email,
            haslo: password,
          }),
        });
        if (response.ok) setIsRegistering(false);
      } else {
        const response = await fetch('/api/person/login', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ mail: email, haslo: password }),
        });
        if (response.ok) navigate('/home');
      }
    }
  };

  return (
    <div className="login-container">
      <header className="login-header"></header>
      <div className="login-content">
        <div className="login-box">
          <h2>{isRegistering ? 'Rejestracja' : 'Logowanie'}</h2>
          <form onSubmit={handleSubmit}>
            {isRegistering && (
              <>
                <TextField
                  label="Imię"
                  fullWidth
                  margin="normal"
                  variant="outlined"
                  value={firstName}
                  onChange={(e) => setFirstName(e.target.value)}
                  error={!!errors.firstName}
                  helperText={errors.firstName}
                />
                <TextField
                  label="Nazwisko"
                  fullWidth
                  margin="normal"
                  variant="outlined"
                  value={lastName}
                  onChange={(e) => setLastName(e.target.value)}
                  error={!!errors.lastName}
                  helperText={errors.lastName}
                />
              </>
            )}
            <TextField
              label="Email"
              fullWidth
              margin="normal"
              variant="outlined"
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              error={!!errors.email}
              helperText={errors.email}
            />
            <TextField
              label="Hasło"
              fullWidth
              margin="normal"
              variant="outlined"
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              error={!!errors.password}
              helperText={errors.password}
            />
            {isRegistering && (
              <TextField
                label="Powtórz Hasło"
                fullWidth
                margin="normal"
                variant="outlined"
                type="password"
                value={confirmPassword}
                onChange={(e) => setConfirmPassword(e.target.value)}
                error={!!errors.confirmPassword}
                helperText={errors.confirmPassword}
              />
            )}
            <Button type="submit" className="login-button" variant="contained">
              {isRegistering ? 'Zarejestruj' : 'Zaloguj'}
            </Button>
            <div className="separator">lub</div>
            <Button
              variant="outlined"
              className="register-button"
              onClick={() => setIsRegistering(!isRegistering)}
            >
              {isRegistering ? 'Wróć do logowania' : 'Utwórz konto'}
            </Button>
          </form>
        </div>
      </div>
    </div>
  );
}

export default LoginForm;
