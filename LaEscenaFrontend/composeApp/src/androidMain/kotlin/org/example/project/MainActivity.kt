package org.example.project


import androidx.compose.foundation.background
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.unit.sp
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.automirrored.filled.ArrowForward
import androidx.compose.material.icons.automirrored.filled.ExitToApp
import androidx.compose.material.icons.filled.*
import androidx.compose.ui.tooling.preview.Preview
import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.compose.ui.graphics.painter.Painter
import androidx.activity.compose.setContent
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier

import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavController
import androidx.navigation.compose.*
import androidx.compose.foundation.Image
import androidx.compose.ui.text.style.TextAlign
import org.jetbrains.compose.resources.painterResource
import laescena.composeapp.generated.resources.Res
import laescena.composeapp.generated.resources.con1
import laescena.composeapp.generated.resources.con2
import laescena.composeapp.generated.resources.IMG_3474
import laescena.composeapp.generated.resources.IMG_4263
import laescena.composeapp.generated.resources.IMG_6064_edited
import laescena.composeapp.generated.resources.IMG_6194
import laescena.composeapp.generated.resources.IMG_6625_edited
import laescena.composeapp.generated.resources.IMG_6633
import laescena.composeapp.generated.resources.IMG_6790
import laescena.composeapp.generated.resources.cc

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

@Preview
@Composable
fun AppNavigation() {
    val navController = rememberNavController()
    val loginViewModel: LoginViewModel = viewModel { LoginViewModel() }
    val userId by loginViewModel.userId.collectAsState()

    NavHost(
        navController = navController,
        startDestination = "home"
    ) {
        composable("home") {
            HomeScreen(navController)
        }

        composable("login") {
            LoginScreen(
                viewModel = loginViewModel,
                onLoginSuccess = { role ->
                    val destino = when (role) {
                        "centrocultural" -> "centroculturaldashboard"
                        "artist" -> "artistdashboard"
                        "superadmin" -> "superadmin"
                        else -> role
                    }

                    navController.navigate(destino) {
                        popUpTo("login") { inclusive = true }
                    }
                },
                onNavigateToRegister = {
                    navController.navigate("register")
                }
            )
        }

        composable("register") {
            RegisterScreen(
                onRegisterSuccess = {
                    navController.navigate("login") {
                        popUpTo("register") { inclusive = true }
                    }
                }
            )
        }

        // Perfiles
        composable("superadmin") { SuperAdminScreen(navController) }
        composable("centrocultural") { CentroCulturalScreen() }
        composable("centroculturaldashboard") { CentroCulturalDashboardScreen(navController) }
        composable("agenda") { AgendaScreen(onBack = { navController.popBackStack() }) }

        // Rutas de pantallas
        composable("eventos") { EventosScreen() }
        composable("galerias") { GaleriaScreen() }
        composable("catalogo") { 
            ArtistasScreen(onArtistClick = { id -> navController.navigate("perfil_artista/$id") }) 
        }
        composable("gestionar_artistas") { GestionarArtistasScreen(navController) }
        composable("perfil_artista/{id}") { backStackEntry ->
            val id = backStackEntry.arguments?.getString("id")?.toIntOrNull() ?: 0
            PublicArtistProfileScreen(id, navController)
        }

        // Dashboard Artista
        composable("artistdashboard") { ArtistScreen(navController) }
        composable("artist_profile") { ArtistProfileScreen(navController) }
        composable("artist_events") { ArtistEventsScreen(navController) }
        composable("artist_messages") { ArtistMessagesScreen(navController) }
        
        // AQUÍ ESTÁ EL CAMBIO: El portafolio ahora usa el ID real del usuario logueado
        composable("artist_portfolio") { 
            ArtistPortfolioScreen(navController, artistaId = userId ?: 0) 
        }
        
        composable("artist_portfolio_form") { ArtistPortfolioFormScreen(navController) }
    }
}

@Composable
fun HomeScreen(navController: NavController) {
    val viewModel: ArtistasViewModel = viewModel { ArtistasViewModel() }
    val artistas by viewModel.artistas.collectAsState()
    val isLoading by viewModel.isLoading.collectAsState()
    var searchQuery by remember { mutableStateOf("") }

    LaunchedEffect(Unit) {
        viewModel.cargarArtistas()
    }

    val artistasFiltrados = artistas.filter {
        it.nombre.contains(searchQuery, ignoreCase = true)
    }

    Column(
        modifier = Modifier
            .fillMaxSize()
            .background(ColorFondo)
    )
         {
            // Header
            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(horizontal = 16.dp, vertical = 12.dp),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                Text(
                    text = "La Escena",
                    fontSize = 22.sp,
                    fontWeight = FontWeight.Bold,
                    color = ColorPrimario
                )
                Row(horizontalArrangement = Arrangement.spacedBy(16.dp)) {
                    IconButton(onClick = { navController.navigate("agenda") }) {
                        Icon(
                            imageVector = Icons.Default.DateRange,
                            contentDescription = "Agenda",
                            tint = ColorTexto
                        )
                    }
                    IconButton(onClick = { navController.navigate("login") }) {
                        Icon(
                            imageVector = Icons.Default.Person,
                            contentDescription = "Perfil",
                            tint = ColorTexto
                        )
                    }
                }
            }

        LazyColumn(
            modifier = Modifier.fillMaxSize(),
            contentPadding = PaddingValues(16.dp),
            verticalArrangement = Arrangement.spacedBy(16.dp)
        ) {
            // Card Centro Cultural
            item {
                Card(
                    modifier = Modifier.fillMaxWidth(),
                    shape = RoundedCornerShape(16.dp),
                    colors = CardDefaults.cardColors(containerColor = ColorSuperficie)
                ) {
                    Box(modifier = Modifier.fillMaxWidth().height(200.dp)) {
                        Box(
                            modifier = Modifier
                                .fillMaxSize()
                                .background(Color(0xFF3A2A2A))
                        )
                        Column(
                            modifier = Modifier
                                .align(Alignment.BottomStart)
                                .padding(16.dp)
                        ) {
                            Text(
                                text = "Centro Cultural",
                                fontSize = 20.sp,
                                fontWeight = FontWeight.Bold,
                                color = ColorTexto
                            )
                            Text(
                                text = "Descubre eventos, exposiciones y actividades culturales",
                                fontSize = 13.sp,
                                color = ColorTextoSecundario
                            )
                            Spacer(modifier = Modifier.height(12.dp))
                            Button(
                                onClick = { navController.navigate("centrocultural") },
                                colors = ButtonDefaults.buttonColors(containerColor = ColorPrimario),
                                shape = RoundedCornerShape(8.dp)
                            ) {
                                Text("Ver más", color = Color.White)
                                Spacer(modifier = Modifier.width(4.dp))
                                Icon(
                                    Icons.AutoMirrored.Filled.ArrowForward,
                                    contentDescription = null,
                                    tint = Color.White,
                                    modifier = Modifier.size(16.dp)
                                )
                            }
                        }
                    }
                }
            }
            item {
                Text(
                    text = "Explorar",
                    fontSize = 18.sp,
                    fontWeight = FontWeight.Bold,
                    color = ColorTexto
                )
                Spacer(modifier = Modifier.height(8.dp))
                Row(
                    modifier = Modifier.fillMaxWidth(),
                    horizontalArrangement = Arrangement.spacedBy(8.dp)
                ) {
                    Button(
                        onClick = { navController.navigate("eventos") },
                        modifier = Modifier.weight(1f),
                        colors = ButtonDefaults.buttonColors(containerColor = ColorPrimario),
                        shape = RoundedCornerShape(8.dp)
                    ) { Text("Eventos", color = Color.White) }

                    Button(
                        onClick = { navController.navigate("galerias") },
                        modifier = Modifier.weight(1f),
                        colors = ButtonDefaults.buttonColors(containerColor = ColorPrimario),
                        shape = RoundedCornerShape(8.dp)
                    ) { Text("Galerías", color = Color.White) }

                }
            }
            // Barra de búsqueda
            item {
                OutlinedTextField(
                    value = searchQuery,
                    onValueChange = { searchQuery = it },
                    placeholder = { Text("Buscar artistas o disciplinas...", color = ColorTextoSecundario) },
                    leadingIcon = {
                        Icon(Icons.Default.Search, contentDescription = null, tint = ColorTextoSecundario)
                    },
                    trailingIcon = {
                        Icon(Icons.Default.Menu, contentDescription = null, tint = ColorTextoSecundario)
                    },
                    modifier = Modifier.fillMaxWidth(),
                    shape = RoundedCornerShape(12.dp),
                    colors = OutlinedTextFieldDefaults.colors(
                        focusedBorderColor = ColorPrimario,
                        unfocusedBorderColor = Color(0xFF444444),
                        focusedTextColor = ColorTexto,
                        unfocusedTextColor = ColorTexto,
                        cursorColor = ColorPrimario
                    )
                )
            }

            // Contador
            item {
                Text(
                    text = "${artistasFiltrados.size} artistas encontrados",
                    color = ColorTextoSecundario,
                    fontSize = 13.sp
                )
            }

            // Lista de artistas
            if (isLoading) {
                item {
                    CircularProgressIndicator(
                        modifier = Modifier.fillMaxWidth().wrapContentWidth(),
                        color = ColorPrimario
                    )
                }
            } else {
                items(artistasFiltrados) { artista ->
                    Card(
                        modifier = Modifier.fillMaxWidth(),
                        onClick = { navController.navigate("perfil_artista/${artista.id}") },
                        shape = RoundedCornerShape(16.dp),
                        colors = CardDefaults.cardColors(containerColor = ColorSuperficie)
                    ) {
                        Box(modifier = Modifier.fillMaxWidth().height(180.dp)) {
                            Box(
                                modifier = Modifier
                                    .fillMaxSize()
                                    .background(Color(0xFF2A2A3A))
                            )
                            // Badge disciplina
                            Box(
                                modifier = Modifier
                                    .align(Alignment.TopEnd)
                                    .padding(12.dp)
                                    .background(ColorPrimario, RoundedCornerShape(20.dp))
                                    .padding(horizontal = 10.dp, vertical = 4.dp)
                            ) {
                                Text("Artista", color = Color.White, fontSize = 11.sp)
                            }
                        }
                        Column(modifier = Modifier.padding(16.dp)) {
                            Row(
                                modifier = Modifier.fillMaxWidth(),
                                horizontalArrangement = Arrangement.SpaceBetween,
                                verticalAlignment = Alignment.CenterVertically
                            ) {
                                Column {
                                    Text(
                                        text = artista.nombre,
                                        fontSize = 17.sp,
                                        fontWeight = FontWeight.Bold,
                                        color = ColorTexto
                                    )
                                    Text(
                                        text = artista.correo,
                                        fontSize = 13.sp,
                                        color = ColorTextoSecundario
                                    )
                                }
                                Icon(
                                    Icons.AutoMirrored.Filled.ArrowForward,
                                    contentDescription = null,
                                    tint = ColorPrimario
                                )
                            }
                        }
                    }
                }
            }
        }
    }
}


@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun SuperAdminScreen(navController: NavController) {
    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Panel Administrador", color = ColorPrimario, fontWeight = FontWeight.Bold) },
                actions = {
                    IconButton(onClick = { navController.navigate("home") { popUpTo(0) } }) {
                        Icon(Icons.AutoMirrored.Filled.ExitToApp, contentDescription = "Cerrar Sesión", tint = ColorPrimario)
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(containerColor = ColorFondo)
            )
        },
        containerColor = ColorFondo
    ) { padding ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(padding)
                .padding(16.dp)
        ) {
            Text(text = "Control total de la plataforma", color = ColorTextoSecundario)
            Spacer(modifier = Modifier.height(20.dp))
            LazyColumn(verticalArrangement = Arrangement.spacedBy(12.dp)) {
                item { AdminCard("Panel Admin", onClick = { /* Panel */ }) }
                item { AdminCard("Gestionar Artistas", onClick = { navController.navigate("gestionar_artistas") }) }
                item { AdminCard("Ver Catalogo", onClick = { navController.navigate("catalogo") }) }
                item { AdminCard("Galeria", onClick = { navController.navigate("galerias") }) }
            }
        }
    }
}

@Composable
fun ArtistCard(titulo: String, onClick: () -> Unit) {
    Card(
        modifier = Modifier.fillMaxWidth(),
        onClick = onClick,
        shape = RoundedCornerShape(16.dp),
        colors = CardDefaults.cardColors(containerColor = ColorSuperficie)
    ) {
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .padding(16.dp),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically
        ) {
            Text(titulo, color = ColorTexto)
            Icon(Icons.AutoMirrored.Filled.ArrowForward, contentDescription = null, tint = ColorTexto)
        }
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ArtistScreen(navController: NavController) {
    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Panel de Artista", color = ColorPrimario, fontWeight = FontWeight.Bold) },
                actions = {
                    IconButton(onClick = { navController.navigate("home") { popUpTo(0) } }) {
                        Icon(Icons.AutoMirrored.Filled.ExitToApp, contentDescription = "Cerrar Sesión", tint = ColorPrimario)
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(containerColor = ColorFondo)
            )
        },
        containerColor = ColorFondo
    ) { padding ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(padding)
                .padding(16.dp)
        ) {
            Text(
                text = "Gestiona tu perfil y contenido",
                color = ColorTextoSecundario
            )

            Spacer(modifier = Modifier.height(20.dp))

            LazyColumn(
                verticalArrangement = Arrangement.spacedBy(12.dp)
            ) {
                item {
                    ArtistCard("Ver eventos", onClick = { navController.navigate("artist_events") })
                }

                item {
                    ArtistCard("Perfil", onClick = { navController.navigate("artist_profile") })
                }

                item {
                    ArtistCard("Mensajes", onClick = { navController.navigate("artist_messages") })
                }

                item {
                    ArtistCard("Portafolio artístico", onClick = { navController.navigate("artist_portfolio") })
                }
            }
        }
    }
}

@Composable
fun CentroCulturalPublicaScreen() {
    Column(
        Modifier.fillMaxSize(),
        verticalArrangement = Arrangement.Center,
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        Text(text = "Centro Cultural")
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Ver eventos") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Crear evento") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Mensajes") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Ver artistas") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Buscar artistas") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Crear galería") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Ver Galerias") }
    }
}

@Composable
fun RegisterScreen(
    onRegisterSuccess: () -> Unit,
    viewModel: LoginViewModel = viewModel { LoginViewModel() }
) {
    var email by remember { mutableStateOf("") }
    var password by remember { mutableStateOf("") }

    val resultMessage by viewModel.loginResult.collectAsState()
    val isRegisterSuccess by viewModel.isRegisterSuccess.collectAsState()

    LaunchedEffect(isRegisterSuccess) {
        if (isRegisterSuccess) {
            onRegisterSuccess()
            viewModel.resetSuccess()
        }
    }

    Column(
        modifier = Modifier
            .fillMaxSize()
            .background(ColorFondo)
            .padding(20.dp),
        verticalArrangement = Arrangement.Center,
        horizontalAlignment = Alignment.CenterHorizontally
    ) {

        Text(
            text = "Crear cuenta",
            fontSize = 26.sp,
            fontWeight = FontWeight.Bold,
            color = ColorPrimario
        )

        Spacer(modifier = Modifier.height(8.dp))

        Text(
            text = "Regístrate como artista",
            color = ColorTextoSecundario
        )

        Spacer(modifier = Modifier.height(30.dp))

        // CARD CONTENEDORA (como en Home)
        Card(
            shape = RoundedCornerShape(16.dp),
            colors = CardDefaults.cardColors(containerColor = ColorSuperficie),
            modifier = Modifier.fillMaxWidth()
        ) {
            Column(modifier = Modifier.padding(16.dp)) {

                OutlinedTextField(
                    value = email,
                    onValueChange = { email = it },
                    label = { Text("Correo") },
                    modifier = Modifier.fillMaxWidth(),
                    shape = RoundedCornerShape(12.dp),
                    colors = OutlinedTextFieldDefaults.colors(
                        focusedBorderColor = ColorPrimario,
                        unfocusedBorderColor = Color(0xFF444444),
                        focusedTextColor = ColorTexto,
                        unfocusedTextColor = ColorTexto,
                        cursorColor = ColorPrimario
                    )
                )

                Spacer(modifier = Modifier.height(12.dp))

                OutlinedTextField(
                    value = password,
                    onValueChange = { password = it },
                    label = { Text("Contraseña") },
                    visualTransformation = PasswordVisualTransformation(),
                    modifier = Modifier.fillMaxWidth(),
                    shape = RoundedCornerShape(12.dp),
                    colors = OutlinedTextFieldDefaults.colors(
                        focusedBorderColor = ColorPrimario,
                        unfocusedBorderColor = Color(0xFF444444),
                        focusedTextColor = ColorTexto,
                        unfocusedTextColor = ColorTexto,
                        cursorColor = ColorPrimario
                    )
                )

                Spacer(modifier = Modifier.height(20.dp))

                Button(
                    onClick = {
                        viewModel.registrar(email, password, "artista")
                    },
                    modifier = Modifier.fillMaxWidth(),
                    shape = RoundedCornerShape(10.dp),
                    colors = ButtonDefaults.buttonColors(containerColor = ColorPrimario)
                ) {
                    Text("Registrarse", color = Color.White)
                }
            }
        }

        Spacer(modifier = Modifier.height(16.dp))

        Text(
            text = resultMessage,
            color = if (resultMessage.contains("Error")) Color.Red else ColorTextoSecundario
        )
    }
}

@Composable
fun DashboardCard(titulo: String, onClick: () -> Unit = {}) {
    Card(
        modifier = Modifier.fillMaxWidth(),
        onClick = onClick,
        shape = RoundedCornerShape(16.dp),
        colors = CardDefaults.cardColors(containerColor = ColorSuperficie)
    ) {
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .padding(16.dp),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically
        ) {
            Text(
                text = titulo,
                fontSize = 16.sp,
                color = ColorTexto,
                fontWeight = FontWeight.Medium
            )

            Icon(
                imageVector = Icons.AutoMirrored.Filled.ArrowForward,
                contentDescription = null,
                tint = ColorPrimario
            )
        }
    }
}
@Composable
fun AdminCard(titulo: String, onClick: () -> Unit = {}) {
    Card(
        modifier = Modifier.fillMaxWidth(),
        onClick = onClick,
        shape = RoundedCornerShape(16.dp),
        colors = CardDefaults.cardColors(containerColor = ColorSuperficie)
    ) {
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .padding(16.dp),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically
        ) {

            Text(
                text = titulo,
                fontSize = 16.sp,
                color = ColorTexto,
                fontWeight = FontWeight.Medium
            )

            Icon(
                imageVector = Icons.AutoMirrored.Filled.ArrowForward,
                contentDescription = null,
                tint = ColorPrimario
            )
        }
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun GestionarArtistasScreen(navController: NavController) {
    val viewModel: ArtistasViewModel = viewModel { ArtistasViewModel() }
    val artistas by viewModel.artistas.collectAsState()
    
    val listaArtistas = remember { mutableStateListOf<Artista>() }
    val aprobados = remember { mutableStateListOf<Int>() }

    LaunchedEffect(artistas) {
        if (artistas.isNotEmpty()) {
            listaArtistas.clear()
            listaArtistas.addAll(artistas)
        }
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Gestionar Artistas", color = ColorTexto) },
                navigationIcon = {
                    IconButton(onClick = { navController.popBackStack() }) {
                        Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Atrás", tint = ColorTexto)
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(containerColor = ColorSuperficie)
            )
        },
        containerColor = ColorFondo
    ) { padding ->
        LazyColumn(modifier = Modifier.padding(padding).padding(16.dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
            items(listaArtistas) { artista ->
                val isAprobado = aprobados.contains(artista.id)
                Card(modifier = Modifier.fillMaxWidth(), colors = CardDefaults.cardColors(containerColor = ColorSuperficie)) {
                    Row(modifier = Modifier.padding(16.dp), verticalAlignment = Alignment.CenterVertically, horizontalArrangement = Arrangement.SpaceBetween) {
                        Column(modifier = Modifier.weight(1f)) {
                            Text(artista.nombre, fontWeight = FontWeight.Bold, color = ColorTexto)
                            Text(if (isAprobado) "Estado: Aprobado" else "Estado: Pendiente", color = if (isAprobado) Color.Green else Color.Yellow, fontSize = 12.sp)
                        }
                        Row {
                            IconButton(onClick = { 
                                if (isAprobado) aprobados.remove(artista.id) else aprobados.add(artista.id)
                            }) {
                                Icon(
                                    imageVector = if (isAprobado) Icons.Default.Close else Icons.Default.Check, 
                                    contentDescription = "Cambiar estado", 
                                    tint = if (isAprobado) Color(0xFFFFA500) else Color.Green
                                )
                            }
                            IconButton(onClick = { /* Lógica editar */ }) {
                                Icon(Icons.Default.Edit, contentDescription = "Editar", tint = Color.LightGray)
                            }
                            IconButton(onClick = { listaArtistas.remove(artista) }) {
                                Icon(Icons.Default.Delete, contentDescription = "Eliminar", tint = Color.Red)
                            }
                        }
                    }
                }
            }
        }
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun PublicArtistProfileScreen(artistaId: Int, navController: NavController) {
    val viewModel: ArtistasViewModel = viewModel { ArtistasViewModel() }
    val artista by viewModel.artistaSeleccionado.collectAsState()
    val isLoading by viewModel.isLoading.collectAsState()

    LaunchedEffect(artistaId) {
        viewModel.cargarArtista(artistaId)
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Perfil de Artista", color = ColorTexto) },
                navigationIcon = {
                    IconButton(onClick = { navController.popBackStack() }) {
                        Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Atrás", tint = ColorTexto)
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(containerColor = ColorSuperficie)
            )
        },
        containerColor = ColorFondo
    ) { padding ->
        if (isLoading) {
            Box(modifier = Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                CircularProgressIndicator(color = ColorPrimario)
            }
        } else {
            artista?.let { a ->
                Column(modifier = Modifier.padding(padding).padding(16.dp)) {
                    Card(modifier = Modifier.fillMaxWidth(), colors = CardDefaults.cardColors(containerColor = ColorSuperficie)) {
                        Column(modifier = Modifier.padding(16.dp)) {
                            Box(modifier = Modifier.fillMaxWidth().height(150.dp).background(ColorPrimario, RoundedCornerShape(8.dp)))
                            Spacer(modifier = Modifier.height(16.dp))
                            Text(a.nombre, fontSize = 24.sp, fontWeight = FontWeight.Bold, color = ColorTexto)
                            Text("Disciplina: Artista Visual", color = ColorTextoSecundario)
                            Text("Contacto: ${a.correo}", color = ColorTextoSecundario)
                            Text("Teléfono: ${a.telefono}", color = ColorTextoSecundario)
                        }
                    }
                    Spacer(modifier = Modifier.height(24.dp))
                    Text("Portafolios", fontSize = 18.sp, fontWeight = FontWeight.Bold, color = ColorTexto)
                    Spacer(modifier = Modifier.height(12.dp))
                    LazyColumn(verticalArrangement = Arrangement.spacedBy(8.dp)) {
                        items(listOf("Portafolio Principal", "Obra 2025")) { p ->
                            Card(modifier = Modifier.fillMaxWidth(), colors = CardDefaults.cardColors(containerColor = ColorSuperficie)) {
                                Text(p, modifier = Modifier.padding(16.dp), color = ColorTexto)
                            }
                        }
                    }
                }
            } ?: Box(modifier = Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                Text("No se encontró la información del artista", color = ColorTexto)
            }
        }
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ArtistEventsScreen(navController: NavController) {
    // Reutilizamos los eventos de ejemplo de la agenda
    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Eventos Bookeados", color = ColorTexto) },
                navigationIcon = {
                    IconButton(onClick = { navController.popBackStack() }) {
                        Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Atrás", tint = ColorTexto)
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(containerColor = ColorSuperficie)
            )
        },
        containerColor = ColorFondo
    ) { padding ->
        LazyColumn(
            modifier = Modifier.padding(padding).padding(16.dp),
            verticalArrangement = Arrangement.spacedBy(12.dp)
        ) {
            items(eventosEjemplo) { evento ->
                Card(
                    modifier = Modifier.fillMaxWidth(),
                    shape = RoundedCornerShape(16.dp),
                    colors = CardDefaults.cardColors(containerColor = ColorSuperficie)
                ) {
                    Column(modifier = Modifier.padding(16.dp)) {
                        Text(evento.titulo, fontWeight = FontWeight.Bold, color = ColorTexto)
                        Text("Artista: ${evento.artista}", color = ColorTextoSecundario)
                        Text("Fecha: ${evento.fecha}", color = ColorTextoSecundario)
                        Text("Lugar: ${evento.lugar}", color = ColorTextoSecundario)
                    }
                }
            }
        }
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ArtistProfileScreen(navController: NavController) {
    var nombreArtistico by remember { mutableStateOf("") }
    var nombreCompleto by remember { mutableStateOf("") }
    var disciplina by remember { mutableStateOf("") }
    var telefono by remember { mutableStateOf("") }
    var contactoPublico by remember { mutableStateOf(true) }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Mi Perfil", color = ColorTexto) },
                navigationIcon = {
                    IconButton(onClick = { navController.popBackStack() }) {
                        Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Atrás", tint = ColorTexto)
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(containerColor = ColorSuperficie)
            )
        },
        containerColor = ColorFondo
    ) { padding ->
        Column(
            modifier = Modifier
                .padding(padding)
                .padding(16.dp)
                .verticalScroll(rememberScrollState())
        ) {
            // Placeholder para fotos
            Row(modifier = Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(16.dp)) {
                Button(onClick = {}, modifier = Modifier.weight(1f), colors = ButtonDefaults.buttonColors(containerColor = ColorSuperficie)) {
                    Text("Foto Perfil", color = ColorTexto)
                }
                Button(onClick = {}, modifier = Modifier.weight(1f), colors = ButtonDefaults.buttonColors(containerColor = ColorSuperficie)) {
                    Text("Foto Portada", color = ColorTexto)
                }
            }

            Spacer(modifier = Modifier.height(16.dp))

            OutlinedTextField(value = nombreArtistico, onValueChange = { nombreArtistico = it }, label = { Text("Nombre artístico") }, modifier = Modifier.fillMaxWidth(), colors = OutlinedTextFieldDefaults.colors(focusedTextColor = ColorTexto, unfocusedTextColor = ColorTexto))
            Spacer(modifier = Modifier.height(8.dp))
            OutlinedTextField(value = nombreCompleto, onValueChange = { nombreCompleto = it }, label = { Text("Nombre completo") }, modifier = Modifier.fillMaxWidth(), colors = OutlinedTextFieldDefaults.colors(focusedTextColor = ColorTexto, unfocusedTextColor = ColorTexto))
            Spacer(modifier = Modifier.height(8.dp))
            OutlinedTextField(value = disciplina, onValueChange = { disciplina = it }, label = { Text("Disciplina") }, modifier = Modifier.fillMaxWidth(), colors = OutlinedTextFieldDefaults.colors(focusedTextColor = ColorTexto, unfocusedTextColor = ColorTexto))
            Spacer(modifier = Modifier.height(8.dp))
            OutlinedTextField(value = telefono, onValueChange = { telefono = it }, label = { Text("Teléfono") }, modifier = Modifier.fillMaxWidth(), colors = OutlinedTextFieldDefaults.colors(focusedTextColor = ColorTexto, unfocusedTextColor = ColorTexto))

            Spacer(modifier = Modifier.height(16.dp))

            Row(verticalAlignment = Alignment.CenterVertically) {
                Text("Contacto visible al público", color = ColorTexto)
                Spacer(modifier = Modifier.weight(1f))
                Switch(checked = contactoPublico, onCheckedChange = { contactoPublico = it })
            }

            Spacer(modifier = Modifier.height(24.dp))

            Button(onClick = {}, modifier = Modifier.fillMaxWidth(), colors = ButtonDefaults.buttonColors(containerColor = ColorPrimario)) {
                Text("Guardar cambios", color = Color.White)
            }
        }
    }
}

data class Mensaje(val remitente: String, val texto: String, val esMio: Boolean)

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ArtistMessagesScreen(navController: NavController) {
    var nuevoMensaje by remember { mutableStateOf("") }
    val mensajes = remember { mutableStateListOf(
        Mensaje("Centro Cultural", "Hola, ¿estás disponible el viernes?", false),
        Mensaje("Tú", "Sí, claro. ¿A qué hora?", true)
    ) }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Mensajería", color = ColorTexto) },
                navigationIcon = {
                    IconButton(onClick = { navController.popBackStack() }) {
                        Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Atrás", tint = ColorTexto)
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(containerColor = ColorSuperficie)
            )
        },
        containerColor = ColorFondo
    ) { padding ->
        Column(modifier = Modifier.padding(padding).padding(16.dp)) {
            LazyColumn(modifier = Modifier.weight(1f), verticalArrangement = Arrangement.spacedBy(8.dp)) {
                items(mensajes) { msg ->
                    Column(modifier = Modifier.fillMaxWidth(), horizontalAlignment = if (msg.esMio) Alignment.End else Alignment.Start) {
                        Card(colors = CardDefaults.cardColors(containerColor = if (msg.esMio) ColorPrimario else ColorSuperficie)) {
                            Text(msg.texto, modifier = Modifier.padding(12.dp), color = Color.White)
                        }
                    }
                }
            }
            Spacer(modifier = Modifier.height(8.dp))
            Row(verticalAlignment = Alignment.CenterVertically) {
                OutlinedTextField(value = nuevoMensaje, onValueChange = { nuevoMensaje = it }, modifier = Modifier.weight(1f), placeholder = { Text("Escribe un mensaje...") }, colors = OutlinedTextFieldDefaults.colors(focusedTextColor = ColorTexto, unfocusedTextColor = ColorTexto)
                )

                Spacer(modifier = Modifier.width(8.dp))

                Button(
                    onClick = {
                        if (nuevoMensaje.isNotBlank()) {
                            mensajes.add(Mensaje("Tú", nuevoMensaje, true))
                            nuevoMensaje = ""
                        }
                    },
                    colors = ButtonDefaults.buttonColors(containerColor = ColorPrimario)
                ) {
                    Text("Enviar", color = Color.White)
                }
            }
        }
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ArtistPortfolioFormScreen(navController: NavController) {
    var disciplina by remember { mutableStateOf("") }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Subir Portafolio", color = ColorTexto) },
                navigationIcon = {
                    IconButton(onClick = { navController.popBackStack() }) {
                        Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Atrás", tint = ColorTexto)
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(containerColor = ColorSuperficie)
            )
        },
        containerColor = ColorFondo
    ) { padding ->
        Column(modifier = Modifier.padding(padding).padding(16.dp)) {
            OutlinedTextField(value = disciplina, onValueChange = { disciplina = it }, label = { Text("Disciplina") }, modifier = Modifier.fillMaxWidth(), colors = OutlinedTextFieldDefaults.colors(focusedTextColor = ColorTexto, unfocusedTextColor = ColorTexto))
            Spacer(modifier = Modifier.height(16.dp))
            Button(onClick = {}, modifier = Modifier.fillMaxWidth(), colors = ButtonDefaults.buttonColors(containerColor = ColorSuperficie)) {
                Text("Seleccionar Foto de Portada", color = ColorTexto)
            }
            Spacer(modifier = Modifier.height(8.dp))
            Button(onClick = {}, modifier = Modifier.fillMaxWidth(), colors = ButtonDefaults.buttonColors(containerColor = ColorSuperficie)) {
                Text("Subir Archivo (PDF/ZIP)", color = ColorTexto)
            }
            Spacer(modifier = Modifier.height(24.dp))
            Button(onClick = { navController.popBackStack() }, modifier = Modifier.fillMaxWidth(), colors = ButtonDefaults.buttonColors(containerColor = ColorPrimario)) {
                Text("Guardar Portafolio", color = Color.White)
            }
        }
    }
}
