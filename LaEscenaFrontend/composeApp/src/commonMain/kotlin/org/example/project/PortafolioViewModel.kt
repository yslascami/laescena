package org.example.project

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch

class PortafolioViewModel : ViewModel() {
    private val api = Apiservice()

    private val _portafolioItems = MutableStateFlow<List<Portafolio>>(emptyList())
    val portafolioItems: StateFlow<List<Portafolio>> get() = _portafolioItems

    private val _isLoading = MutableStateFlow(false)
    val isLoading: StateFlow<Boolean> get() = _isLoading

    private val _error = MutableStateFlow("")
    val error: StateFlow<String> get() = _error

    private val _uploadSuccess = MutableStateFlow(false)
    val uploadSuccess: StateFlow<Boolean> get() = _uploadSuccess

    fun cargarPortafolio(artistaId: Int) {
        viewModelScope.launch {
            _isLoading.value = true
            try {
                val resultado = api.getPortafolio(artistaId)
                _portafolioItems.value = resultado
                _error.value = ""
            } catch (e: Exception) {
                _error.value = "Error al cargar el portafolio"
            } finally {
                _isLoading.value = false
            }
        }
    }

    fun crearNuevoPortafolio(
        artistaId: Int, 
        nombreArtista: String, 
        titulo: String, 
        descripcion: String, 
        tipo: String, 
        nombreArchivo: String, 
        archivoBytes: ByteArray
    ) {
        viewModelScope.launch {
            _isLoading.value = true
            try {
                val response = api.crearPortafolioConArchivo(
                    artistaId = artistaId,
                    nombreArtista = nombreArtista,
                    titulo = titulo,
                    descripcion = descripcion,
                    tipo = tipo,
                    nombreArchivo = nombreArchivo,
                    archivoBytes = archivoBytes
                )
                if (response.success) {
                    _uploadSuccess.value = true
                    _error.value = ""
                } else {
                    _error.value = response.message
                }
            } catch (e: Exception) {
                _error.value = "Error al subir: ${e.message}"
            } finally {
                _isLoading.value = false
            }
        }
    }

    // --- NUEVA FUNCIÓN PARA ELIMINAR ---
    fun eliminarPortafolio(portafolioId: Int, artistaId: Int) {
        viewModelScope.launch {
            _isLoading.value = true
            try {
                val response = api.eliminarPortafolio(portafolioId)
                if (response.success) {
                    // Recargamos la lista después de borrar
                    cargarPortafolio(artistaId)
                } else {
                    _error.value = response.message
                }
            } catch (e: Exception) {
                _error.value = "Error al eliminar"
            } finally {
                _isLoading.value = false
            }
        }
    }

    fun resetUploadSuccess() {
        _uploadSuccess.value = false
    }
}
