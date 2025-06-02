import React, { useEffect, useState } from 'react';
import {
  Box,
  Button,
  Typography,
  CircularProgress,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  TextField,
  IconButton,
  Paper,
  Grid,
} from '@mui/material';
import Header from './Header';
import NavMenu from './NavMenu';
import FolderIcon from '@mui/icons-material/Folder';
import DeleteIcon from '@mui/icons-material/Delete';
import EditIcon from '@mui/icons-material/Edit';
import ArrowBackIcon from '@mui/icons-material/ArrowBack';
import InsertDriveFileIcon from '@mui/icons-material/InsertDriveFile';

const QuotesFilesPage = () => {
  const [treeData, setTreeData] = useState([]);
  const [currentFolderId, setCurrentFolderId] = useState(null);
  const [parentStack, setParentStack] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState(null);

  const [showNewFolderModal, setShowNewFolderModal] = useState(false);
  const [newFolderName, setNewFolderName] = useState('');
  const [showRenameModal, setShowRenameModal] = useState(false);
  const [renameTarget, setRenameTarget] = useState(null);
  const [renameValue, setRenameValue] = useState('');
  const [deleteTarget, setDeleteTarget] = useState(null);
  const [deleteTargetType, setDeleteTargetType] = useState('folder');
  const [currentFolderName, setCurrentFolderName] = useState('Główny');
  const [uploadingFiles, setUploadingFiles] = useState([]);
  
  const fetchChildren = async (parentId = null, pushStack = false) => {
    setIsLoading(true);
    setError(null);
    try {
      const url = `/api/upload/${parentId ?? 0}/children`;
      const res = await fetch(url);
      if (!res.ok) throw new Error('Błąd pobierania folderów');
      const data = await res.json();
      setTreeData(data);

      if (parentId && parentId !== 0) {
        const folderRes = await fetch(`/api/upload/${parentId}`);
        if (folderRes.ok) {
          const folderData = await folderRes.json();
          setCurrentFolderName(folderData.name || '...');
        } else {
          setCurrentFolderName('...');
        }
      } else {
        setCurrentFolderName('Główny');
      }

      if (pushStack && currentFolderId) {
        setParentStack(prev => [...prev, currentFolderId]);
      }
      setCurrentFolderId(parentId);
    } catch (e) {
      setError(e.message);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchChildren(null);
  }, []);

  const handleEnterFolder = (folderId) => {
    fetchChildren(folderId, true);
  };

  const handleBack = () => {
    if (parentStack.length === 0) {
      fetchChildren(null);
      setParentStack([]);
    } else {
      const prev = [...parentStack];
      const parentId = prev.pop();
      setParentStack(prev);
      fetchChildren(parentId || null, false);
    }
  };

  const handleCreateFolder = async () => {
    try {
      await fetch('/api/upload/folder', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: newFolderName, parent: currentFolderId }),
      });
      setShowNewFolderModal(false);
      setNewFolderName('');
      fetchChildren(currentFolderId);
    } catch (e) {
      setError('Błąd tworzenia folderu');
    }
  };

  const handleRename = async () => {
    try {
      await fetch(`/api/upload/${renameTarget}/rename`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: renameValue }),
      });
      setShowRenameModal(false);
      setRenameTarget(null);
      setRenameValue('');
      fetchChildren(currentFolderId);
    } catch (e) {
      setError('Błąd zmiany nazwy');
    }
  };

  const handleDelete = async () => {
    try {
      await fetch(`/api/upload/${deleteTarget}`, {
        method: 'DELETE',
      });
      setDeleteTarget(null);
      fetchChildren(currentFolderId);
    } catch (e) {
      setError('Błąd usuwania');
    }
  };

  const handleDrop = async (draggedId, targetId) => {
    if (!draggedId || !targetId || draggedId === targetId) return;
    await fetch(`/api/upload/${draggedId}/move`, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ parent: targetId }),
    });
    fetchChildren(currentFolderId);
  };

  const handleFileUpload = async (e) => {
    const file = e.target.files[0];
    if (!file) return;
    if (!['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/x-iwork-numbers-sffnumbers'].includes(file.type) &&
        !file.name.endsWith('.xlsx') && !file.name.endsWith('.numbers')) {
      setError('Dozwolone tylko pliki .xlsx lub .numbers');
      return;
    }

    setUploadingFiles(prev => [...prev, { name: file.name, status: 'uploading', id: null }]);

    const formData = new FormData();
    formData.append('file', file);
    formData.append('parent', currentFolderId ?? '');

    try {
      const res = await fetch('/api/upload', {
        method: 'POST',
        body: formData,
      });
      if (!res.ok) throw new Error('Błąd wysyłania pliku');
      const data = await res.json();

      setUploadingFiles(prev => prev.map(f =>
        f.name === file.name && !f.id ? { ...f, id: data.id } : f
      ));

      pollUploadStatus(data.id, file.name);
    } catch (e) {
      setUploadingFiles(prev => prev.filter(f => f.name !== file.name));
      setError('Błąd wysyłania pliku');
    }
  };

  const pollUploadStatus = (id, fileName) => {
    const interval = setInterval(async () => {
      const res = await fetch(`/api/upload/${id}/status`);
      if (!res.ok) return;
      const data = await res.json();
      if (data.status === 'done' || data.status === 'error') {
        setUploadingFiles(prev => prev.filter(f => f.id !== id));
        fetchChildren(currentFolderId);
        clearInterval(interval);
      }
    }, 2000);
  };



  return (
    <div className="home-container">
      <Header />
      <Box className="home-content" sx={{ display: 'flex' }}>
        <NavMenu />
        <Box className="main-content" sx={{ flex: 1, padding: 3 }}>
          <Typography variant="h4" gutterBottom>
            Wyceny - Foldery
          </Typography>

          <Box sx={{ mb: 2, display: 'flex', gap: 2 }}>
            <Button variant="contained" onClick={() => setShowNewFolderModal(true)}>
              Nowy folder
            </Button>
            <Button
            variant="outlined"
            component="label"
          >
            Dodaj plik
            <input
              type="file"
              accept=".xlsx,.numbers"
              hidden
              onChange={handleFileUpload}
            />
          </Button>
            {currentFolderId && (
              <Button
                variant="outlined"
                startIcon={<ArrowBackIcon />}
                onClick={handleBack}
              >
                Wstecz
              </Button>
            )}
          </Box>

          <Box sx={{ mb: 2 }}>
            <Typography variant="subtitle1" sx={{ fontWeight: 'bold' }}>
              {currentFolderName}
            </Typography>
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
            treeData.length === 0 ? (
              <Typography>Brak folderów.</Typography>
            ) : (
              <Grid container spacing={2}>
                {treeData.map(node => (
                  <Grid item key={node.id} xs={12} sm={6} md={4} lg={3}>
                    <Paper
                      elevation={3}
                      sx={{
                        p: 2,
                        display: 'flex',
                        alignItems: 'center',
                        gap: 2,
                        cursor: node.type === 'folder' ? 'pointer' : 'default',
                        position: 'relative',
                      }}
                      onClick={() => node.type === 'folder' && handleEnterFolder(node.id)}
                      draggable
                      onDragStart={e => {
                        e.dataTransfer.setData('uploadId', node.id);
                      }}
                      onDrop={async e => {
                        e.preventDefault();
                        const draggedId = e.dataTransfer.getData('uploadId');
                        if (draggedId && node.type === 'folder') {
                          await handleDrop(draggedId, node.id);
                        }
                      }}
                      onDragOver={e => {
                        if (node.type === 'folder') e.preventDefault();
                      }}
                    >
                      {node.type === 'folder' ? <FolderIcon fontSize="large" /> : <InsertDriveFileIcon fontSize="large" />}
                      <span style={{ flex: 1 }}>{node.name}</span>
                      <IconButton
                        size="small"
                        onClick={e => {
                          e.stopPropagation();
                          setRenameTarget(node.id);
                          setRenameValue(node.name);
                          setShowRenameModal(true);
                        }}
                      >
                        <EditIcon fontSize="small" />
                      </IconButton>
                      <IconButton
                        size="small"
                        onClick={e => {
                          e.stopPropagation();
                          setDeleteTarget(node.id);
                          setDeleteTargetType(node.type);
                        }}
                      >
                        <DeleteIcon fontSize="small" />
                      </IconButton>
                    </Paper>
                  </Grid>
                ))}
              </Grid>
            )
          )}

          

          {/* Modal nowego folderu */}
          <Dialog open={showNewFolderModal} onClose={() => setShowNewFolderModal(false)}>
            <DialogTitle>Nowy folder</DialogTitle>
            <DialogContent>
              <TextField
                autoFocus
                label="Nazwa folderu"
                value={newFolderName}
                onChange={e => setNewFolderName(e.target.value)}
                fullWidth
              />
            </DialogContent>
            <DialogActions>
              <Button onClick={() => setShowNewFolderModal(false)}>Anuluj</Button>
              <Button
                variant="contained"
                disabled={!newFolderName}
                onClick={handleCreateFolder}
              >Utwórz</Button>
            </DialogActions>
          </Dialog>

          {/* Modal zmiany nazwy */}
          <Dialog open={showRenameModal} onClose={() => setShowRenameModal(false)}>
            <DialogTitle>Zmień nazwę</DialogTitle>
            <DialogContent>
              <TextField
                autoFocus
                label="Nowa nazwa"
                value={renameValue}
                onChange={e => setRenameValue(e.target.value)}
                fullWidth
              />
            </DialogContent>
            <DialogActions>
              <Button onClick={() => setShowRenameModal(false)}>Anuluj</Button>
              <Button
                variant="contained"
                disabled={!renameValue}
                onClick={handleRename}
              >Zmień</Button>
            </DialogActions>
          </Dialog>

          {/* Modal potwierdzenia usuwania */}
          <Dialog open={!!deleteTarget} onClose={() => setDeleteTarget(null)}>
            <DialogTitle>
              {deleteTargetType === 'folder'
                ? 'Czy na pewno chcesz usunąć folder?'
                : 'Czy na pewno chcesz usunąć plik?'}
            </DialogTitle>
            <DialogActions>
              <Button onClick={() => setDeleteTarget(null)}>Anuluj</Button>
              <Button color="error" variant="contained" onClick={handleDelete}>Usuń</Button>
            </DialogActions>
          </Dialog>
        </Box>
      </Box>

      <Box
        sx={{
          position: 'fixed',
          right: 24,
          bottom: 24,
          zIndex: 1300,
          minWidth: 250,
        }}
      >
        {uploadingFiles.map(f => (
          <Paper
            key={f.name}
            sx={{
              mb: 1,
              p: 2,
              display: 'flex',
              alignItems: 'center',
              gap: 2,
              background: '#1976d2',
              color: 'white',
            }}
            elevation={6}
          >
            <CircularProgress size={20} sx={{ color: 'white' }} />
            <span>Wysyłanie {f.name}</span>
          </Paper>
        ))}
      </Box>
    </div>
  );
};

export default QuotesFilesPage;
