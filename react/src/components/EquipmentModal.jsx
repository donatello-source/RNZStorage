import React, { useState, useEffect } from 'react';
import { Modal, Box, TextField, Button, MenuItem } from '@mui/material';

const EquipmentModal = ({ open, onClose, equipment, categories = [] }) => {
  const [formData, setFormData] = useState({
    id: '',
    name: '',
    description: '',
    quantity: '',
    price: '',
    categoryid: '',
    additional_info: '',
    pricing_info: ''
  });

  useEffect(() => {
    if (equipment && categories.length > 0) {
      setFormData({
        id: equipment.id || '',
        name: equipment.name || '',
        description: equipment.description || '',
        quantity: equipment.quantity || '',
        price: equipment.price || '',
        categoryid: equipment.categoryid ? String(equipment.categoryid) : String(categories[0].id),
        additional_info: equipment.additional_info || '',
        pricing_info: equipment.pricing_info || ''
      });
    } else if (!equipment && categories.length > 0) {
      setFormData({
        id: '',
        name: '',
        description: '',
        quantity: '',
        price: '',
        categoryid: String(categories[0].id),
        additional_info: '',
        pricing_info: ''
      });
    }
  }, [equipment, categories, open]);

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleSave = async () => {
    const url = formData.id ? `/api/equipment/${formData.id}` : '/api/equipment';
    const method = formData.id ? 'PUT' : 'POST';
  
    const response = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formData),
    });
  
    if (response.ok) {
      onClose();
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
        <TextField label="Informacje Dodatkowe" fullWidth margin="normal" name="additional_info" value={formData.additional_info} onChange={handleChange} />
        <TextField label="Informacje do Wyceny" fullWidth margin="normal" name="pricing_info" value={formData.pricing_info} onChange={handleChange} />
        
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
