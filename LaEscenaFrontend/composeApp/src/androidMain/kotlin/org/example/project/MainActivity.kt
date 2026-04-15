package org.example.project

import android.os.Bundle
import android.net.Uri
import android.provider.OpenableColumns
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.activity.compose.rememberLauncherForActivityResult
import androidx.activity.result.contract.ActivityResultContracts
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.lazy.rememberLazyListState
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.*
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.runtime.getValue
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavController
import androidx.navigation.compose.*
import androidx.compose.foundation.Image
import androidx.compose.ui.graphics.painter.Painter
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.platform.LocalContext
import org.jetbrains.compose.resources.painterResource

// Import de recursos
import laescena.composeapp.generated.resources.Res
import laescena.composeapp.generated.resources.*

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContent {
            MaterialTheme {
                AppNavigation()
            }
        }
    }
}

fun getFileName(context: android.content.Context, uri: Uri): String {
    var name = "archivo_desconocido"
    val cursor = context.contentResolver.query(uri, null, null, null, null)
    cursor?.use {
        if (it.moveToFirst()) {
            val nameIndex = it.getColumnIndex(OpenableColumns.DISPLAY_NAME)
            if (nameIndex != -1) name = it.getString(nameIndex)
        }
    }
    return name
}

@Composable
fun AppNavigation() {
    val navController = rememberNavController()
    val loginViewModel: LoginViewModel = viewModel { LoginViewModel() }
    val userId by loginViewModel.userId.collectAsState()
    val userRole by loginViewModel.userRole.collectAsState()

    NavHost(navController = navController, startDestination = "home") {
        composable("home") { HomeScreen(navController) }
        composable("login") {
            LoginScreen(
                viewModel = loginViewModel,
                onLoginSuccess = { role ->
                    val destino = when (role) {
                        "centrocultural" -> "centroculturaldashboard"
                        "artista" -> "artistdashboard"
                        "superadmin" -> "superadmin"
                        else -> "home"
                    }
                    navController.navigate(destino) { popUpTo("login") { inclusive = true } }
                },
                onNavigateToRegister = { navController.navigate("register") }
            )
        }
        composable("register") {
            RegisterScreen(onRegisterSuccess = { 
                navController.navigate("login") { popUpTo("register") { inclusive = true } } 
            })
        }
        composable("superadmin") { SuperAdminScreen(navController) }
        composable("centrocultural") { CentroCulturalScreen(navController) }
        composable("centroculturaldashboard") { CentroCulturalDashboardScreen(navController) }
        composable("agenda") { AgendaScreen(onBack = { navController.popBackStack() }) }
        composable("eventos") { EventosScreen(navController) }
        composable("galerias") { GaleriaScreen(navController) }
        
        composable("catalogo") { 
            ArtistasScreen(onArtistClick = { id -> 
                navController.navigate("perfil_artista/$id")
            }) 
        }

        composable("mensajeria_cc") {
            ArtistasScreen(onArtistClick = { id -> 
                navController.navigate("chat/$id")
            })
        }

        composable("gestionar_artistas") { GestionarArtistasScreen(navController) }
        composable("perfil_artista/{id}") { backStackEntry ->
            val id = backStackEntry.arguments?.getString("id")?.toIntOrNull() ?: 0
            PublicArtistProfileScreen(id, navController)
        }
        
        composable("artist_messages") { 
            ArtistMessagesScreen(navController, userId ?: 0, userRole) 
        }
        
        composable("chat/{artistaId}") { backStackEntry ->
            val artistaId = backStackEntry.arguments?.getString("artistaId")?.toIntOrNull() ?: 0
            ArtistMessagesScreen(navController, artistaId, userRole)
        }

        composable("artistdashboard") { ArtistScreen(navController) }
        composable("artist_profile") { ArtistProfileScreen(navController) }
        composable("artist_events") { ArtistEventsScreen(navController) }
        
        composable("artist_portfolio") { 
            ArtistPortfolioScreen(navController, artistaId = userId ?: 0) 
        }
        composable("artist_portfolio_form") { 
            ArtistPortfolioFormScreen(navController, userId = userId ?: 0) 
        }
    }
}

@Composable
fun HomeScreen(navController: NavController) {
    val viewModel: ArtistasViewModel = viewModel { ArtistasViewModel() }
    val artistas by viewModel.artistas.collectAsState()
    var searchQuery by remember { mutableStateOf("") }

    LaunchedEffect(Unit) { viewModel.cargarArtistas() }

    val artistasFiltrados = artistas.filter { it.nombre.contains(searchQuery, ignoreCase = true) }

    Column(modifier = Modifier.fillMaxSize().background(ColorFondo)) {
        Row(modifier = Modifier.fillMaxWidth().padding(16.dp), horizontalArrangement = Arrangement.SpaceBetween, verticalAlignment = Alignment.CenterVertically) {
            Text(text = "La Escena", fontSize = 24.sp, fontWeight = FontWeight.Bold, color = ColorPrimario)
            Row(horizontalArrangement = Arrangement.spacedBy(16.dp)) {
                IconButton(onClick = { navController.navigate("agenda") }) { Icon(Icons.Default.DateRange, contentDescription = null, tint = ColorTexto) }
                IconButton(onClick = { navController.navigate("login") }) { Icon(Icons.Default.Person, contentDescription = null, tint = ColorTexto) }
            }
        }

        LazyColumn(modifier = Modifier.fillMaxSize(), contentPadding = PaddingValues(16.dp), verticalArrangement = Arrangement.spacedBy(20.dp)) {
            item {
                Card(modifier = Modifier.fillMaxWidth(), shape = RoundedCornerShape(16.dp), colors = CardDefaults.cardColors(containerColor = ColorSuperficie)) {
                    Box(modifier = Modifier.fillMaxWidth().height(200.dp)) {
                        Box(modifier = Modifier.fillMaxSize().background(Color(0xFF3A2A2A)))
                        Column(modifier = Modifier.align(Alignment.BottomStart).padding(16.dp)) {
                            Text("Centro Cultural", fontSize = 20.sp, fontWeight = FontWeight.Bold, color = ColorTexto)
                            Text("Descubre actividades culturales", fontSize = 13.sp, color = ColorTextoSecundario)
                            Spacer(modifier = Modifier.height(12.dp))
                            Button(onClick = { navController.navigate("centrocultural") }, colors = ButtonDefaults.buttonColors(containerColor = ColorPrimario)) {
                                Text("Ver más", color = Color.White)
                            }
                        }
                    }
                }
            }
            item {
                Row(modifier = Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(12.dp)) {
                    Button(onClick = { navController.navigate("eventos") }, modifier = Modifier.weight(1f), colors = ButtonDefaults.buttonColors(containerColor = ColorPrimario), shape = RoundedCornerShape(12.dp)) { Text("Eventos", color = Color.White) }
                    Button(onClick = { navController.navigate("galerias") }, modifier = Modifier.weight(1f), colors = ButtonDefaults.buttonColors(containerColor = ColorPrimario), shape = RoundedCornerShape(12.dp)) { Text("Galerías", color = Color.White) }
                }
            }
            items(artistasFiltrados) { artista ->
                Card(modifier = Modifier.fillMaxWidth(), onClick = { navController.navigate("perfil_artista/${artista.id}") }, colors = CardDefaults.cardColors(containerColor = ColorSuperficie), shape = RoundedCornerShape(16.dp)) {
                    Row(modifier = Modifier.padding(16.dp), verticalAlignment = Alignment.CenterVertically) {
                        Box(modifier = Modifier.size(50.dp).background(ColorPrimario, RoundedCornerShape(8.dp)))
                        Spacer(modifier = Modifier.width(16.dp))
                        Column {
                            Text(artista.nombre, fontWeight = FontWeight.Bold, color = ColorTexto, fontSize = 16.sp)
                            Text(artista.correo, color = ColorTextoSecundario, fontSize = 13.sp)
                        }
                    }
                }
            }
        }
    }
}

@Composable
fun EventosScreen(navController: NavController) {
    Column(modifier = Modifier.fillMaxSize().background(ColorFondo).padding(16.dp).verticalScroll(rememberScrollState())) {
        Text("Eventos en existencia", style = MaterialTheme.typography.headlineLarge, color = ColorTexto)
        Spacer(modifier = Modifier.height(20.dp))
        Card(colors = CardDefaults.cardColors(containerColor = ColorSuperficie), shape = RoundedCornerShape(16.dp)) {
            Column(modifier = Modifier.padding(16.dp)) {
                Image(painterResource(Res.drawable.con1), contentDescription = null, modifier = Modifier.fillMaxWidth().height(180.dp))
                Spacer(modifier = Modifier.height(12.dp))
                Text("Anper Bajo el radar", fontWeight = FontWeight.Bold, color = ColorTexto, fontSize = 18.sp)
                Text("25 Mar 2026", color = ColorPrimario, fontSize = 14.sp)
            }
        }
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun GaleriaScreen(navController: NavController) {
    Scaffold(topBar = { TopAppBar(title = { Text("Galerías", color = ColorPrimario) }, colors = TopAppBarDefaults.topAppBarColors(containerColor = ColorFondo)) }, containerColor = ColorFondo) { padding ->
        Column(modifier = Modifier.padding(padding).fillMaxSize().padding(16.dp).verticalScroll(rememberScrollState())) {
            GaleriaPic("Recuerdo borroso", painterResource(Res.drawable.IMG_3474))
            Spacer(modifier = Modifier.height(16.dp))
            GaleriaPic("Me entiende más un caballo", painterResource(Res.drawable.IMG_4263))
        }
    }
}

@Composable
fun GaleriaPic(titulo: String, imagen: Painter) {
    Card(colors = CardDefaults.cardColors(containerColor = ColorSuperficie), shape = RoundedCornerShape(16.dp)) {
        Column(modifier = Modifier.padding(12.dp)) {
            Image(imagen, contentDescription = null, modifier = Modifier.fillMaxWidth())
            Spacer(modifier = Modifier.height(8.dp))
            Text(titulo, fontWeight = FontWeight.Bold, color = ColorTexto)
        }
    }
}

@Composable
fun CentroCulturalScreen(navController: NavController) {
    Column(modifier = Modifier.fillMaxSize().background(ColorFondo).padding(24.dp).verticalScroll(rememberScrollState())) {
        Image(painterResource(Res.drawable.cc), contentDescription = null, modifier = Modifier.fillMaxWidth().height(200.dp))
        Spacer(modifier = Modifier.height(20.dp))
        Text("Centro Cultural Ricardo Garibay", fontSize = 24.sp, fontWeight = FontWeight.Bold, color = ColorTexto)
        Text("Promoción de la cultura local.", color = ColorTextoSecundario)
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun CentroCulturalDashboardScreen(navController: NavController) {
    Scaffold(topBar = { TopAppBar(title = { Text("Dashboard CC", color = ColorPrimario) }, colors = TopAppBarDefaults.topAppBarColors(containerColor = ColorFondo)) }, containerColor = ColorFondo) { padding ->
        Column(modifier = Modifier.padding(padding).padding(16.dp)) {
            DashboardCard("Ver Catálogo de Artistas", onClick = { navController.navigate("catalogo") })
            DashboardCard("Bandeja de Mensajería", onClick = { navController.navigate("mensajeria_cc") })
            DashboardCard("Calendario de Eventos", onClick = { navController.navigate("eventos") })
        }
    }
}

@Composable
fun DashboardCard(titulo: String, onClick: () -> Unit) {
    Card(modifier = Modifier.fillMaxWidth().padding(vertical = 8.dp), onClick = onClick, colors = CardDefaults.cardColors(containerColor = ColorSuperficie), shape = RoundedCornerShape(12.dp)) {
        Row(modifier = Modifier.padding(20.dp), verticalAlignment = Alignment.CenterVertically) {
            Text(titulo, color = ColorTexto, modifier = Modifier.weight(1f))
            Icon(Icons.AutoMirrored.Filled.ArrowForward, contentDescription = null, tint = ColorPrimario)
        }
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun SuperAdminScreen(navController: NavController) {
    Scaffold(topBar = { TopAppBar(title = { Text("Panel Administrador", color = ColorPrimario) }, colors = TopAppBarDefaults.topAppBarColors(containerColor = ColorFondo)) }, containerColor = ColorFondo) { padding ->
        Column(modifier = Modifier.padding(padding).padding(16.dp)) {
            DashboardCard("Gestionar todos los Artistas", onClick = { navController.navigate("gestionar_artistas") })
        }
    }
}

@Composable
fun ArtistScreen(navController: NavController) {
    Column(modifier = Modifier.fillMaxSize().background(ColorFondo).padding(16.dp)) {
        Text("Panel de Artista", fontSize = 24.sp, fontWeight = FontWeight.Bold, color = ColorPrimario)
        Spacer(modifier = Modifier.height(20.dp))
        DashboardCard("Mi Portafolio Artístico", onClick = { navController.navigate("artist_portfolio") })
        DashboardCard("Mi Perfil Público", onClick = { navController.navigate("artist_profile") })
        DashboardCard("Mensajes Recibidos", onClick = { navController.navigate("artist_messages") })
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun PublicArtistProfileScreen(artistaId: Int, navController: NavController) {
    val viewModel: ArtistasViewModel = viewModel { ArtistasViewModel() }
    val artista by viewModel.artistaSeleccionado.collectAsState()
    LaunchedEffect(artistaId) { viewModel.cargarArtista(artistaId) }
    Scaffold(topBar = { TopAppBar(title = { Text("Perfil", color = ColorPrimario) }, colors = TopAppBarDefaults.topAppBarColors(containerColor = ColorFondo), navigationIcon = { IconButton(onClick = { navController.popBackStack() }) { Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = null, tint = ColorPrimario) } }) }, containerColor = ColorFondo) { padding ->
        artista?.let { a ->
            Column(modifier = Modifier.padding(padding).padding(24.dp)) {
                Text(a.nombre, fontSize = 28.sp, fontWeight = FontWeight.Bold, color = ColorTexto)
                Text(a.correo, color = ColorTextoSecundario)
            }
        }
    }
}

@Composable
fun ArtistProfileScreen(navController: NavController) {
    Box(modifier = Modifier.fillMaxSize().background(ColorFondo), contentAlignment = Alignment.Center) {
        Text("Editar Mi Perfil", color = ColorTexto)
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ArtistMessagesScreen(navController: NavController, artistaId: Int, userRole: String) {
    val viewModel: MensajesViewModel = viewModel { MensajesViewModel() }
    val mensajes by viewModel.mensajes.collectAsState()
    var nuevoMensaje by remember { mutableStateOf("") }
    val listState = rememberLazyListState()

    LaunchedEffect(artistaId) { viewModel.cargarMensajes(artistaId) }

    LaunchedEffect(mensajes.size) {
        if (mensajes.isNotEmpty()) {
            listState.animateScrollToItem(mensajes.size - 1)
        }
    }

    Scaffold(
        topBar = { TopAppBar(title = { Text("Chat", color = ColorPrimario) }, colors = TopAppBarDefaults.topAppBarColors(containerColor = ColorFondo), navigationIcon = { IconButton(onClick = { navController.popBackStack() }) { Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = null, tint = ColorPrimario) } }) },
        containerColor = ColorFondo
    ) { padding ->
        Column(modifier = Modifier.padding(padding).fillMaxSize().padding(16.dp)) {
            LazyColumn(
                state = listState,
                modifier = Modifier.weight(1f), 
                verticalArrangement = Arrangement.spacedBy(12.dp)
            ) {
                items(mensajes) { msg ->
                    val soyElRemitente = (msg.remitente == "artista" && userRole == "artista") || 
                                        (msg.remitente == "centrocultural" && userRole == "centrocultural")
                    
                    Column(modifier = Modifier.fillMaxWidth(), horizontalAlignment = if (soyElRemitente) Alignment.End else Alignment.Start) {
                        Box(
                            modifier = Modifier
                                .background(
                                    color = if (soyElRemitente) Color(0xFFF28B82) else ColorSuperficie,
                                    shape = RoundedCornerShape(
                                        topStart = 16.dp, topEnd = 16.dp,
                                        bottomStart = if (soyElRemitente) 16.dp else 0.dp,
                                        bottomEnd = if (soyElRemitente) 0.dp else 16.dp
                                    )
                                )
                                .padding(horizontal = 16.dp, vertical = 10.dp)
                        ) {
                            Text(text = msg.mensaje, color = if (soyElRemitente) Color.Black else Color.White, fontSize = 15.sp)
                        }
                    }
                }
            }
            Row(Modifier.padding(top = 12.dp), verticalAlignment = Alignment.CenterVertically) {
                OutlinedTextField(value = nuevoMensaje, onValueChange = { nuevoMensaje = it }, modifier = Modifier.weight(1f), placeholder = { Text("Escribe un mensaje...") }, colors = OutlinedTextFieldDefaults.colors(focusedTextColor = ColorTexto, unfocusedTextColor = ColorTexto))
                IconButton(onClick = { 
                    if (nuevoMensaje.isNotBlank()) {
                        viewModel.enviarNuevoMensaje(artistaId, if (userRole == "artista") "artista" else "centrocultural", "Chat", nuevoMensaje)
                        nuevoMensaje = ""
                    }
                }) { Icon(Icons.AutoMirrored.Filled.Send, contentDescription = null, tint = ColorPrimario) }
            }
        }
    }
}

@Composable
fun ArtistEventsScreen(navController: NavController) {
    Box(modifier = Modifier.fillMaxSize().background(ColorFondo), contentAlignment = Alignment.Center) {
        Text("Mis Eventos", color = ColorTexto)
    }
}

@Composable
fun GestionarArtistasScreen(navController: NavController) {
    Box(modifier = Modifier.fillMaxSize().background(ColorFondo), contentAlignment = Alignment.Center) {
        Text("Gestionar Artistas", color = ColorTexto)
    }
}

@Composable
fun RegisterScreen(onRegisterSuccess: () -> Unit) {
    Column(modifier = Modifier.fillMaxSize().background(ColorFondo).padding(24.dp), horizontalAlignment = Alignment.CenterHorizontally, verticalArrangement = Arrangement.Center) {
        Text("Crea tu cuenta", fontSize = 26.sp, fontWeight = FontWeight.Bold, color = ColorPrimario)
        Spacer(modifier = Modifier.height(24.dp))
        Button(onClick = onRegisterSuccess, colors = ButtonDefaults.buttonColors(containerColor = ColorPrimario)) { Text("Registrarse", color = Color.White) }
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ArtistPortfolioScreen(navController: NavController, artistaId: Int) {
    val portafolioViewModel: PortafolioViewModel = viewModel { PortafolioViewModel() }
    val items by portafolioViewModel.portafolioItems.collectAsState()
    LaunchedEffect(artistaId) { portafolioViewModel.cargarPortafolio(artistaId) }
    Scaffold(topBar = { TopAppBar(title = { Text("Mi Portafolio", color = ColorPrimario) }, colors = TopAppBarDefaults.topAppBarColors(containerColor = ColorFondo)) }, floatingActionButton = { FloatingActionButton(onClick = { navController.navigate("artist_portfolio_form") }, containerColor = ColorPrimario) { Icon(Icons.Default.Add, contentDescription = null, tint = Color.White) } }, containerColor = ColorFondo) { padding ->
        LazyColumn(Modifier.fillMaxSize().padding(padding).padding(16.dp), verticalArrangement = Arrangement.spacedBy(16.dp)) {
            items(items) { item ->
                Card(colors = CardDefaults.cardColors(containerColor = ColorSuperficie)) {
                    Row(modifier = Modifier.padding(16.dp), verticalAlignment = Alignment.CenterVertically) {
                        Column(Modifier.weight(1f)) {
                            Text(item.titulo, fontWeight = FontWeight.Bold, color = ColorTexto)
                            Text(item.descripcion, color = ColorTextoSecundario)
                        }
                        IconButton(onClick = { portafolioViewModel.eliminarPortafolio(item.id, artistaId) }) {
                            Icon(Icons.Default.Delete, contentDescription = "Eliminar", tint = ColorPrimario)
                        }
                    }
                }
            }
        }
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ArtistPortfolioFormScreen(navController: NavController, userId: Int) {
    val context = LocalContext.current
    val viewModel: PortafolioViewModel = viewModel { PortafolioViewModel() }
    val artistasViewModel: ArtistasViewModel = viewModel { ArtistasViewModel() }
    var titulo by remember { mutableStateOf("") }
    var descripcion by remember { mutableStateOf("") }
    var tipo by remember { mutableStateOf("Imagen") }
    var selectedUri by remember { mutableStateOf<Uri?>(null) }
    
    val artista by artistasViewModel.artistaSeleccionado.collectAsState()
    val uploadSuccess by viewModel.uploadSuccess.collectAsState()
    val isLoading by viewModel.isLoading.collectAsState()

    val launcher = rememberLauncherForActivityResult(contract = ActivityResultContracts.GetContent()) { uri: Uri? ->
        selectedUri = uri
    }

    LaunchedEffect(userId) { artistasViewModel.cargarArtista(userId) }
    LaunchedEffect(uploadSuccess) { if (uploadSuccess) { navController.popBackStack(); viewModel.resetUploadSuccess() } }

    Scaffold(topBar = { TopAppBar(title = { Text("Nueva Obra", color = ColorPrimario) }, colors = TopAppBarDefaults.topAppBarColors(containerColor = ColorFondo), navigationIcon = { IconButton(onClick = { navController.popBackStack() }) { Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = null, tint = ColorPrimario) } }) }, containerColor = ColorFondo) { padding ->
        Column(Modifier.padding(padding).padding(16.dp).verticalScroll(rememberScrollState()), verticalArrangement = Arrangement.spacedBy(16.dp)) {
            if (isLoading) {
                LinearProgressIndicator(modifier = Modifier.fillMaxWidth(), color = ColorPrimario)
                Text("Enviando archivo...", color = ColorPrimario, fontSize = 14.sp, modifier = Modifier.fillMaxWidth(), textAlign = TextAlign.Center)
            }

            OutlinedTextField(value = titulo, onValueChange = { titulo = it }, label = { Text("Título") }, modifier = Modifier.fillMaxWidth(), colors = OutlinedTextFieldDefaults.colors(focusedTextColor = ColorTexto, unfocusedTextColor = ColorTexto))
            OutlinedTextField(value = descripcion, onValueChange = { descripcion = it }, label = { Text("Descripción") }, modifier = Modifier.fillMaxWidth(), colors = OutlinedTextFieldDefaults.colors(focusedTextColor = ColorTexto, unfocusedTextColor = ColorTexto))
            
            Text("Tipo de obra:", color = ColorTexto)
            Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                listOf("Imagen", "Video", "PDF").forEach { option ->
                    FilterChip(selected = tipo == option, onClick = { tipo = option }, label = { Text(option) })
                }
            }

            Card(modifier = Modifier.fillMaxWidth(), colors = CardDefaults.cardColors(containerColor = ColorSuperficie)) {
                Column(Modifier.padding(16.dp), horizontalAlignment = Alignment.CenterHorizontally) {
                    Icon(Icons.Default.CloudUpload, contentDescription = null, tint = ColorPrimario, modifier = Modifier.size(40.dp))
                    Button(onClick = { 
                        val mimeType = when(tipo) {
                            "Imagen" -> "image/*"
                            "Video" -> "video/*"
                            "PDF" -> "application/pdf"
                            else -> "*/*"
                        }
                        launcher.launch(mimeType) 
                    }, colors = ButtonDefaults.buttonColors(containerColor = ColorPrimario), enabled = !isLoading) {
                        Text("Elegir de mi teléfono")
                    }
                    if (selectedUri != null) {
                        Text("Archivo listo para subir", color = Color.Green, fontSize = 12.sp)
                    }
                }
            }

            Button(
                onClick = { 
                    selectedUri?.let { uri ->
                        val inputStream = context.contentResolver.openInputStream(uri)
                        val bytes = inputStream?.readBytes() ?: byteArrayOf()
                        val fileName = getFileName(context, uri)
                        viewModel.crearNuevoPortafolio(userId, artista?.nombre ?: "Autor", titulo, descripcion, tipo, fileName, bytes)
                    }
                }, 
                modifier = Modifier.fillMaxWidth().height(50.dp), 
                enabled = titulo.isNotBlank() && selectedUri != null && !isLoading, 
                colors = ButtonDefaults.buttonColors(containerColor = ColorPrimario)
            ) {
                if (isLoading) CircularProgressIndicator(color = Color.White)
                else Text("Guardar Portafolio")
            }
        }
    }
}
