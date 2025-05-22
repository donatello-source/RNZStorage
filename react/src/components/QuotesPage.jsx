import React, { useEffect, useState } from 'react';
import {
  Box,
  Button,
  Typography,
  List,
  ListItem,
  ListItemText,
  CircularProgress,
} from '@mui/material';
import Header from './Header';
import NavMenu from './NavMenu';

const QuotesPage = () => {
  const [uploads, setUploads] = useState([]);
  const [selectedFile, setSelectedFile] = useState(null);
  const [uploading, setUploading] = useState(false);
  const [uploadName, setUploadName] = useState('');
  const [error, setError] = useState(null);
  const [isLoading, setIsLoading] = useState(true);

  const fetchUploads = async () => {
    try {
      const res = await fetch('/api/upload');
      if (!res.ok) throw new Error('Błąd pobierania plików');
      const data = await res.json();
      setUploads(data);
    } catch (e) {
      setError(e.message);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchUploads();
  }, []);

  const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
      const allowedExt = ['xlsx', 'numbers'];
      const ext = file.name.split('.').pop().toLowerCase();
      if (!allowedExt.includes(ext)) {
        setError('Dozwolone formaty: xlsx, numbers');
        return;
      }
      setSelectedFile(file);
      setError(null);
    }
  };

  const handleUpload = async () => {
    if (!selectedFile) return;

    setUploading(true);
    setUploadName(selectedFile.name);
    setError(null);

    const formData = new FormData();
    formData.append('file', selectedFile);

    try {
      const res = await fetch('/api/upload', {
        method: 'POST',
        body: formData,
      });
      if (!res.ok) throw new Error('Błąd podczas wysyłania pliku');

      await fetchUploads();
      setSelectedFile(null);
      setUploadName('');
    } catch (e) {
      setError(e.message);
    } finally {
      setUploading(false);
    }
  };

  return (
    <div className="home-container">
      <Header />
      <Box className="home-content" sx={{ display: 'flex' }}>
        <NavMenu />
        <Box className="main-content" sx={{ flex: 1, padding: 3 }}>
          <Typography variant="h4" gutterBottom>
            Wyceny - Pliki
          </Typography>

          <Box sx={{ mb: 2 }}>
            <input
              type="file"
              accept=".xlsx,.numbers"
              onChange={handleFileChange}
              disabled={uploading}
              style={{ display: 'inline-block', marginRight: 10 }}
            />
            <Button variant="contained" onClick={handleUpload} disabled={!selectedFile || uploading}>
              Wyślij plik
            </Button>
          </Box>

          {error && (
            <Typography color="error" sx={{ mb: 2 }}>
              {error}
            </Typography>
          )}

          {isLoading ? (
            <Box sx={{ display: 'flex', justifyContent: 'center', mt: 5 }}>
              <CircularProgress />
            </Box>
          ) : (
            <List>
              {uploads.length === 0 && <Typography>Brak plików na serwerze.</Typography>}
              {uploads.map((upload) => (
                <ListItem
                  key={upload.id}
                  secondaryAction={
                    upload.status === 'done' && (
                      <Button
                        variant="outlined"
                        href={upload.download_url}
                        target="_blank"
                        rel="noopener noreferrer"
                      >
                        Pobierz
                      </Button>
                    )
                  }
                >
                  <ListItemText
                    primary={upload.original_name}
                    secondary={`Status: ${upload.status}${upload.error ? `, Błąd: ${upload.error}` : ''}`}
                  />
                </ListItem>
              ))}
            </List>
          )}

          {uploading && (
            <Box
              sx={{
                position: 'fixed',
                bottom: 20,
                right: 20,
                backgroundColor: 'rgba(0,0,0,0.7)',
                color: 'white',
                px: 2,
                py: 1,
                borderRadius: 2,
                display: 'flex',
                alignItems: 'center',
                gap: 1,
                zIndex: 1000,
              }}
            >
              <CircularProgress size={20} color="inherit" />
              <Typography variant="body2">{uploadName}</Typography>
            </Box>
          )}
        </Box>
      </Box>
    </div>
  );
};

export default QuotesPage;
