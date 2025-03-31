import React, { useState, useEffect } from 'react';
import { Modal, Box, TextField, Button, MenuItem } from '@mui/material';

const EquipmentModal = ({ open, onClose, equipment, onSave }) => {
  const [formData, setFormData] = useState({
    id: '',
    name: '',
    description: '',
    quantity: '',
    price: '',
    categoryid: ''
  });

  const [categories, setCategories] = useState([]);

  useEffect(() => {
    const fetchCategories = async () => {
      try {
        const response = await fetch('/api/category');
        const data = await response.json();
        console.log(data);
        setCategories(data);
      } catch (error) {
        console.error('Błąd pobierania kategorii:', error);
      }
    };

    fetchCategories();
  }, []);

  useEffect(() => {
    if (equipment) {
      setFormData({
        id: equipment.id || '',
        name: equipment.name || '',
        description: equipment.description || '',
        quantity: equipment.quantity || '',
        price: equipment.price || '',
        categoryid: equipment.categoryid || ''
      });
    }
  }, [equipment]);

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleSave = async () => {
    try {
      const response = await fetch(`/api/equipment/${formData.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
      });

      if (!response.ok) {
        throw new Error('Błąd podczas zapisywania zmian');
      }

      const result = await response.json();
      console.log('Zapisano zmiany:', result);

      onSave(result.data);
      onClose();
    } catch (error) {
      console.error('Błąd zapisu:', error);
    }
  };

  return (
    <Modal open={open} onClose={onClose}>
      <Box sx={{
        position: 'absolute',
        top: '50%',
        left: '50%',
        transform: 'translate(-50%, -50%)',
        width: 400,
        bgcolor: 'white',
        boxShadow: 24,
        p: 4,
        borderRadius: '10px'
      }}>
        <h2>Edytuj sprzęt</h2>
        <TextField label="Nazwa" fullWidth margin="normal" name="name" value={formData.name} onChange={handleChange} />
        <TextField label="Opis" fullWidth margin="normal" name="description" value={formData.description} onChange={handleChange} />
        <TextField label="Ilość" fullWidth margin="normal" name="quantity" type="number" value={formData.quantity} onChange={handleChange} />
        <TextField label="Cena" fullWidth margin="normal" name="price" type="number" value={formData.price} onChange={handleChange} />
        
        {/* Select do wyboru kategorii */}
        <TextField
          label="Kategoria"
          select
          fullWidth
          margin="normal"
          name="categoryid"
          value={formData.categoryid}
          onChange={handleChange}
        >
          <MenuItem value="">Brak</MenuItem>
          {categories.map((category) => (
            <MenuItem key={category.id} value={category.id}>
              {category.nazwa}
            </MenuItem>
          ))}
        </TextField>

        <Box sx={{ display: 'flex', justifyContent: 'space-between', marginTop: 2 }}>
          <Button variant="outlined" color="secondary" onClick={onClose}>Anuluj</Button>
          <Button variant="contained" color="primary" onClick={handleSave}>Zapisz</Button>
        </Box>
      </Box>
    </Modal>
  );
};

export default EquipmentModal;
