import React, { useState, useEffect } from 'react';
import {
  Box,
  Grid,
  TextField,
  Button,
  Typography,
  MenuItem,
  IconButton,
  Paper,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Checkbox,
  List,
  ListItem,
  ListItemText,
} from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import RemoveIcon from '@mui/icons-material/Remove';
import NavMenu from './NavMenu';
import Header from './Header';

const emptyDateRow = { type: 'single', value: '', comment: '' };

const CreateQuotePage = () => {
  const [companies, setCompanies] = useState([]);
  const [zamawiajacy, setZamawiajacy] = useState('');
  const [projekt, setProjekt] = useState('');
  const [lokalizacja, setLokalizacja] = useState('');
  const [dates, setDates] = useState([{ ...emptyDateRow }]);
  const [equipmentTables, setEquipmentTables] = useState([]);
  const [globalDiscount, setGlobalDiscount] = useState(0);
  const [equipmentList, setEquipmentList] = useState([]);
  const [modalOpen, setModalOpen] = useState(false);
  const [modalTableIdx, setModalTableIdx] = useState(null);
  const [equipmentFilter, setEquipmentFilter] = useState('');
  const [categoryFilter, setCategoryFilter] = useState('');
  const [selectedEquipment, setSelectedEquipment] = useState([]);

  useEffect(() => {
    const fetchCompanies = async () => {
      try {
        const res = await fetch('/api/company', { credentials: 'include' });
        const data = await res.json();
        setCompanies(data);
      } catch (e) {
        setCompanies([]);
      }
    };
    fetchCompanies();
    const fetchEquipment = async () => {
      try {
        const res = await fetch('/api/equipment', { credentials: 'include' });
        const data = await res.json();
        setEquipmentList(data);
      } catch (e) {
        setEquipmentList([]);
      }
    };
    fetchEquipment();
  }, []);

  const handleDateChange = (idx, field, value) => {
    setDates(dates =>
      dates.map((row, i) =>
        i === idx ? { ...row, [field]: value } : row
      )
    );
  };

  const addDateRow = () => setDates(dates => [...dates, { ...emptyDateRow }]);
  const removeDateRow = idx =>
    setDates(dates => dates.length > 1 ? dates.filter((_, i) => i !== idx) : dates);

  const handleSubmit = e => {
    e.preventDefault();
    // Submit logic
    console.log({ zamawiajacy, projekt, lokalizacja, dates });
  };

  const netTotal = equipmentTables.reduce(
    (sum, table) =>
      sum +
      table.items.reduce(
        (s, item) =>
          s +
          (item.price || 0) *
            (item.count || 1) *
            (item.days || 1) *
            (1 - (item.discountItem || 0) / 100) *
            (1 - (item.discountTable || 0) / 100),
        0
      ),
    0
  );

  const netTotalAfterDiscount = netTotal * (1 - globalDiscount / 100);

  return (
    <div className="create-quote-container">
      <Header minimized />
      <Box sx={{ display: 'flex' }}>
        <NavMenu minimized />
        <Box
          sx={{
            flex: 1,
            p: 3,
            display: 'flex',
            justifyContent: 'center',
            alignItems: 'flex-start',
            background: '#f5f5f5',
            minHeight: '100vh',
          }}
        >
          <Paper
            elevation={3}
            sx={{
              width: '100%',
              maxWidth: 900,
              p: 4,
              borderRadius: 3,
              minHeight: 400,
            }}
          >
            <form onSubmit={handleSubmit}>
              <Typography variant="h5" gutterBottom>
                Kreator wyceny
              </Typography>
              <Grid container spacing={2} sx={{ mb: 3 }} direction={{ xs: 'column'}}>
                <Grid item xs={12}>
                  <Grid container alignItems="center">
                    <Grid item xs={3}>
                      <Box sx={{ fontWeight: 600 }}>Zamawiający</Box>
                    </Grid>
                    <Grid item xs={9}>
                      <TextField
                        select
                        fullWidth
                        value={zamawiajacy}
                        onChange={e => setZamawiajacy(e.target.value)}
                        size="small"
                      >
                        {companies.map(firma => (
                          <MenuItem key={firma.id} value={firma.id}>
                            {firma.nazwa}
                          </MenuItem>
                        ))}
                      </TextField>
                    </Grid>
                  </Grid>
                </Grid>
                <Grid item xs={12}>
                  <Grid container alignItems="center">
                    <Grid item xs={3}>
                      <Box sx={{ fontWeight: 600 }}>Projekt</Box>
                    </Grid>
                    <Grid item xs={9}>
                      <TextField
                        fullWidth
                        value={projekt}
                        onChange={e => setProjekt(e.target.value)}
                        size="small"
                      />
                    </Grid>
                  </Grid>
                </Grid>
                <Grid item xs={12}>
                  <Grid container alignItems="center">
                    <Grid item xs={3}>
                      <Box sx={{ fontWeight: 600 }}>Lokalizacja</Box>
                    </Grid>
                    <Grid item xs={9}>
                      <TextField
                        fullWidth
                        value={lokalizacja}
                        onChange={e => setLokalizacja(e.target.value)}
                        size="small"
                      />
                    </Grid>
                  </Grid>
                </Grid>
              </Grid>
              <Box sx={{ mt: 4 }}>
                <Grid container spacing={1} alignItems="stretch">
                  <Grid item xs={2}>
                    <Box
                      sx={{
                        height: '100%',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        fontWeight: 600,
                        borderRight: '1px solid #ddd',
                        minHeight: 56 * dates.length,
                      }}
                    >
                      Data
                    </Box>
                  </Grid>
                  <Grid item xs={10}>
                    {dates.map((row, idx) => (
                      <Grid
                        container
                        spacing={1}
                        alignItems="center"
                        key={idx}
                        sx={{ mb: 1 }}
                      >
                        <Grid item xs={4}>
                          <TextField
                            select
                            value={row.type}
                            onChange={e =>
                              handleDateChange(idx, 'type', e.target.value)
                            }
                            size="small"
                            sx={{ width: '100%' }}
                          >
                            <MenuItem value="single">Jeden dzień</MenuItem>
                            <MenuItem value="range">Przedział</MenuItem>
                          </TextField>
                        </Grid>
                        <Grid item xs={4}>
                          <TextField
                            type={row.type === 'single' ? 'date' : 'text'}
                            placeholder={
                              row.type === 'single'
                                ? 'Data'
                                : 'Przedział (np. 13-15.03.2025)'
                            }
                            value={row.value}
                            onChange={e =>
                              handleDateChange(idx, 'value', e.target.value)
                            }
                            size="small"
                            sx={{ width: '100%' }}
                          />
                        </Grid>
                        <Grid item xs={3}>
                          <TextField
                            placeholder="Komentarz"
                            value={row.comment}
                            onChange={e =>
                              handleDateChange(idx, 'comment', e.target.value)
                            }
                            size="small"
                            sx={{ width: '100%' }}
                          />
                        </Grid>
                        <Grid item xs={1}>
                          <IconButton
                            onClick={() => removeDateRow(idx)}
                            disabled={dates.length === 1}
                            size="small"
                          >
                            <RemoveIcon fontSize="small" />
                          </IconButton>
                          {idx === dates.length - 1 && (
                            <IconButton onClick={addDateRow} size="small">
                              <AddIcon fontSize="small" />
                            </IconButton>
                          )}
                        </Grid>
                      </Grid>
                    ))}
                  </Grid>
                </Grid>
              </Box>
              <Box sx={{ mt: 4 }}>
                <Typography variant="h6" gutterBottom>
                  Sprzęt
                </Typography>
                <Button
                  variant="outlined"
                  startIcon={<AddIcon />}
                  onClick={() =>
                    setEquipmentTables([
                      ...equipmentTables,
                      {
                        label: '',
                        items: [],
                        discount: 0,
                        selectedEquipment: [],
                      },
                    ])
                  }
                  sx={{ mb: 2 }}
                >
                  Dodaj tabelkę
                </Button>
                {equipmentTables.map((table, tableIdx) => (
                  <Grid container spacing={2} alignItems="flex-start" sx={{ mb: 4 }} key={tableIdx}>
                    <Grid item xs={2}>
                      <TextField
                        label="Kategoria"
                        value={table.label}
                        onChange={e => {
                          const newTables = [...equipmentTables];
                          newTables[tableIdx].label = e.target.value;
                          setEquipmentTables(newTables);
                        }}
                        size="small"
                        fullWidth
                      />
                    </Grid>
                    <Grid item xs={10}>
                      <Button
                        variant="outlined"
                        startIcon={<AddIcon />}
                        sx={{ mb: 2 }}
                        onClick={() => {
                          setModalTableIdx(tableIdx);
                          setSelectedEquipment(equipmentTables[tableIdx].selectedEquipment || []);
                          setModalOpen(true);
                        }}
                      >
                        Dodaj sprzęt
                      </Button>
                      <Box sx={{ overflowX: 'auto' }}>
                        <table style={{ width: '100%', borderCollapse: 'collapse' }}>
                          <thead>
                            <tr>
                              <th>Lp.</th>
                              <th>Nazwa</th>
                              <th>Liczba</th>
                              <th>Dni</th>
                              <th>Cena jedn.</th>
                              <th>Rabat</th>
                              <th>Łącznie</th>
                              <th>Koszt</th>
                            </tr>
                          </thead>
                          <tbody>
                            {table.items.map((item, idx) => {
                              const total =
                                (item.price || 0) *
                                (item.count || 1) *
                                (item.days || 1) *
                                (1 - (item.discountItem || 0) / 100) *
                                (1 - (table.discount || 0) / 100);
                              return (
                                <tr key={idx}>
                                  <td>{idx + 1}</td>
                                  <td>{item.name}</td>
                                  <td>
                                    <TextField
                                      type="number"
                                      value={item.count}
                                      size="small"
                                      onChange={e => {
                                        const newTables = [...equipmentTables];
                                        newTables[tableIdx].items[idx].count = Number(e.target.value);
                                        setEquipmentTables(newTables);
                                      }}
                                      inputProps={{ min: 1, style: { width: 60 } }}
                                    />
                                  </td>
                                  <td>
                                    <TextField
                                      type="number"
                                      value={item.days}
                                      size="small"
                                      onChange={e => {
                                        const newTables = [...equipmentTables];
                                        newTables[tableIdx].items[idx].days = Number(e.target.value);
                                        setEquipmentTables(newTables);
                                      }}
                                      inputProps={{ min: 1, style: { width: 60 } }}
                                    />
                                  </td>
                                  <td>{item.price?.toFixed(2) ?? ''}</td>
                                  <td>
                                    <TextField
                                      type="number"
                                      value={item.discountItem}
                                      size="small"
                                      onChange={e => {
                                        const newTables = [...equipmentTables];
                                        newTables[tableIdx].items[idx].discountItem = Number(e.target.value);
                                        setEquipmentTables(newTables);
                                      }}
                                      inputProps={{ min: 0, max: 100, style: { width: 60 } }}
                                    />
                                  </td>
                                  <td>
                                    {(
                                      (item.price || 0) *
                                      (item.count || 1) *
                                      (item.days || 1)
                                    ).toFixed(2)}
                                  </td>
                                  <td>{total.toFixed(2)}</td>
                                </tr>
                              );
                            })}
                          </tbody>
                        </table>
                      </Box>
                      {/* Rabat pod tabelką */}
                      <Box sx={{ mt: 2, display: 'flex', alignItems: 'center', justifyContent: 'flex-end', gap: 2 }}>
                        <Box>Rabat (%)</Box>
                        <TextField
                          type="number"
                          value={table.discount || 0}
                          onChange={e => {
                            const newTables = [...equipmentTables];
                            newTables[tableIdx].discount = Number(e.target.value);
                            setEquipmentTables(newTables);
                          }}
                          size="small"
                          inputProps={{ min: 0, max: 100, style: { width: 60 } }}
                        />
                      </Box>
                      <Box sx={{ mt: 2, textAlign: 'right' }}>
                        <Typography>
                          Suma tabelki:{" "}
                          {table.items
                            .reduce(
                              (sum, item) =>
                                sum +
                                (item.price || 0) *
                                  (item.count || 1) *
                                  (item.days || 1) *
                                  (1 - (item.discountItem || 0) / 100) *
                                  (1 - (table.discount || 0) / 100),
                              0
                            )
                            .toFixed(2)}{" "}
                          zł
                        </Typography>
                      </Box>
                    </Grid>
                  </Grid>
                ))}
                {/* MODAL WYBORU SPRZĘTU */}
                <Dialog open={modalOpen} onClose={() => setModalOpen(false)} maxWidth="md" fullWidth>
                  <DialogTitle>Wybierz sprzęt</DialogTitle>
                  <DialogContent>
                    <Box sx={{ display: 'flex', gap: 2, mb: 2 }}>
                      <TextField
                        label="Filtruj po kategorii (ID)"
                        value={categoryFilter}
                        onChange={e => setCategoryFilter(e.target.value)}
                        size="small"
                      />
                      <TextField
                        label="Szukaj po nazwie"
                        value={equipmentFilter}
                        onChange={e => setEquipmentFilter(e.target.value)}
                        size="small"
                      />
                    </Box>
                    <List>
                      {equipmentList
                        .filter(e =>
                          (!categoryFilter || String(e.categoryid) === categoryFilter) &&
                          (!equipmentFilter || e.name.toLowerCase().includes(equipmentFilter.toLowerCase()))
                        )
                        .map(e => (
                          <ListItem
                            key={e.id}
                            button
                            onClick={() => {
                              setSelectedEquipment(prev =>
                                prev.includes(e.id)
                                  ? prev.filter(id => id !== e.id)
                                  : [...prev, e.id]
                              );
                            }}
                          >
                            <Checkbox checked={selectedEquipment.includes(e.id)} />
                            <ListItemText primary={e.name} secondary={e.description} />
                          </ListItem>
                        ))}
                    </List>
                  </DialogContent>
                  <DialogActions>
                    <Button onClick={() => setModalOpen(false)}>Anuluj</Button>
                    <Button
                      onClick={() => {
                        if (modalTableIdx === null) return;
                        const newTables = [...equipmentTables];
                        newTables[modalTableIdx].selectedEquipment = [...selectedEquipment];
                        const itemsToAdd = equipmentList
                          .filter(e => selectedEquipment.includes(e.id))
                          .map(e => {
                            const existing = newTables[modalTableIdx].items.find(item => item.id === e.id);
                            return existing || {
                              ...e,
                              count: 1,
                              days: 1,
                              discountItem: 0,
                              showComment: true,
                            };
                          });
                        newTables[modalTableIdx].items = itemsToAdd;
                        setEquipmentTables(newTables);
                        setModalOpen(false);
                      }}
                      variant="contained"
                    >
                      Dodaj wybrane
                    </Button>
                  </DialogActions>
                </Dialog>
                <Paper sx={{ p: 2 }}>
                <Typography variant="subtitle1">Dodatkowe Informacje</Typography>
                {equipmentTables.flatMap(table =>
                  table.items
                    .filter(item => item.pricing_info)
                    .map((item, idx) => (
                      <Box key={table.label + idx} sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                        <Box sx={{ flex: 1 }}>
                          <b>{item.name}</b> - {item.pricing_info}
                        </Box>
                        <Box>
                          <input
                            type="checkbox"
                            checked={item.showComment}
                            onChange={e => {
                              const newTables = [...equipmentTables];
                              const tIdx = equipmentTables.indexOf(table);
                              newTables[tIdx].items[idx].showComment = e.target.checked;
                              setEquipmentTables(newTables);
                            }}
                          />{" "}
                          Widoczność
                        </Box>
                      </Box>
                    ))
                )}
              </Paper>
              </Box>
              {/* Podsumowanie globalne */}
              <Paper sx={{ p: 2, mb: 2 }}>
                <Typography variant="subtitle1">Podsumowanie</Typography>
                <Box sx={{ display: 'flex', gap: 2, alignItems: 'center', mb: 1 }}>
                  <Box>Rabat całościowy (%)</Box>
                  <TextField
                    type="number"
                    value={globalDiscount}
                    onChange={e => setGlobalDiscount(Number(e.target.value))}
                    size="small"
                    inputProps={{ min: 0, max: 100, style: { width: 60 } }}
                  />
                </Box>
                <Typography>
                  Suma netto: {equipmentTables.reduce(
                    (sum, table) =>
                      sum +
                      table.items.reduce(
                        (s, item) =>
                          s +
                          (item.price || 0) *
                            (item.count || 1) *
                            (item.days || 1) *
                            (1 - (item.discountItem || 0) / 100) *
                            (1 - (table.discount || 0) / 100),
                        0
                      ),
                    0
                  ).toFixed(2)} zł
                </Typography>
                <Typography>
                  Suma netto po rabacie: {(equipmentTables.reduce(
                    (sum, table) =>
                      sum +
                      table.items.reduce(
                        (s, item) =>
                          s +
                          (item.price || 0) *
                            (item.count || 1) *
                            (item.days || 1) *
                            (1 - (item.discountItem || 0) / 100) *
                            (1 - (table.discount || 0) / 100),
                        0
                      ),
                    0
                  ) * (1 - globalDiscount / 100)).toFixed(2)} zł
                </Typography>
                <Typography>
                  Suma brutto (23% VAT): {(equipmentTables.reduce(
                    (sum, table) =>
                      sum +
                      table.items.reduce(
                        (s, item) =>
                          s +
                          (item.price || 0) *
                            (item.count || 1) *
                            (item.days || 1) *
                            (1 - (item.discountItem || 0) / 100) *
                            (1 - (table.discount || 0) / 100),
                        0
                      ),
                    0
                  ) * (1 - globalDiscount / 100) * 1.23).toFixed(2)} zł
                </Typography>
              </Paper>
              <Box sx={{ mt: 4, textAlign: 'right' }}>
                <Button variant="contained" color="primary" type="submit">
                  Zapisz wycenę
                </Button>
              </Box>
            </form>
          </Paper>
        </Box>
      </Box>
    </div>
  );
};

export default CreateQuotePage;