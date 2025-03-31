import React, { useState } from 'react';
import { Card, CardContent, Typography } from '@mui/material';
import EquipmentModal from './EquipmentModal';

const EquipmentCard = ({ equipment }) => {
  const [open, setOpen] = useState(false);

  return (
    <>
      <Card 
        sx={{ width: 'auto', margin: '10px', backgroundColor: '#e0e0e0', cursor: 'pointer' }}
        onClick={() => setOpen(true)}
      >
        <CardContent>
          <Typography variant="h6">{equipment.name}</Typography>
          <Typography variant="body2">Liczba: {equipment.quantity}</Typography>
          <Typography variant="body2">Cena: {equipment.price} PLN</Typography>
        </CardContent>
      </Card>
      <EquipmentModal open={open} onClose={() => setOpen(false)} equipment={equipment} onSave={(updatedEquipment) => {
        console.log('Zapisano zmiany:', updatedEquipment);
      }} />
    </>
  );
};

export default EquipmentCard;