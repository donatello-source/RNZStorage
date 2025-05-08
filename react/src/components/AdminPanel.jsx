import React, { useEffect, useState } from 'react';
import {
  Button, Select, MenuItem, InputLabel,
  FormControl, Table, TableBody, TableCell,
  TableHead, TableRow, Typography
} from '@mui/material';
import { useNavigate } from 'react-router-dom';

function AdminPanel() {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();

  const handleLogout = async () => {
    localStorage.removeItem('user');
    document.cookie = 'PHPSESSID=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
    navigate('/');
  };
  
  useEffect(() => {
    fetch('/api/admin/users', {
      method: 'GET',
      credentials: 'include'
    })
      .then(res => {
        if (res.status === 403 || res.status === 401) {
          navigate('/home');
        }
        return res.json();
      })
      .then(data => {
        setUsers(data.data);
        setLoading(false);
      });
  }, [navigate]);

  const handleRoleChange = (userId, newRoles) => {
    fetch(`/api/admin/users/${userId}/roles`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
      body: JSON.stringify({ roles: newRoles }),
    }).then(() => {
      setUsers(users.map(user =>
        user.id === userId ? { ...user, roles: newRoles } : user
      ));
    });
  };

  if (loading) return <Typography>Ładowanie...</Typography>;

  return (
    <div style={{ padding: '2rem' }}>
      <Typography variant="h4" gutterBottom>Panel administratora</Typography>
      <Table>
        <TableHead>
          <TableRow>
            <TableCell>Imię</TableCell>
            <TableCell>Nazwisko</TableCell>
            <TableCell>Email</TableCell>
            <TableCell>Role</TableCell>
          </TableRow>
        </TableHead>
        <TableBody>
          {users.map(user => (
            <TableRow key={user.id}>
              <TableCell>{user.imie}</TableCell>
              <TableCell>{user.nazwisko}</TableCell>
              <TableCell>{user.mail}</TableCell>
              <TableCell>
                <FormControl fullWidth>
                  <InputLabel>Role</InputLabel>
                  <Select
                    value={user.roles[0] || ''}
                    onChange={(e) => handleRoleChange(user.id, e.target.value)}
                  >
                    <MenuItem value="ROLE_USER">ROLE_USER</MenuItem>
                    <MenuItem value="ROLE_ADMIN">ROLE_ADMIN</MenuItem>
                  </Select>
                </FormControl>
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    <Button className="logout-button" variant="contained" onClick={handleLogout}>
    Wyloguj się
    </Button>
    </div>
  );
}

export default AdminPanel;
