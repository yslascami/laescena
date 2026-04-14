package org.example.project

import androidx.compose.foundation.background
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.unit.sp
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.ArrowForward
import androidx.compose.material.icons.filled.DateRange
import androidx.compose.material.icons.filled.Menu
import androidx.compose.material.icons.filled.Person
import androidx.compose.material.icons.filled.Search

import androidx.compose.ui.tooling.preview.Preview
import androidx.compose.ui.tooling.preview.Devices

import android.os.Bundle
import android.util.Log
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
import androidx.compose.ui.text.font.FontWeight
// Agrega esta línea con el nombre de tu imagen
import laescena.composeapp.generated.resources.con1
// Agrega esta línea con el nombre de tu imagen
import laescena.composeapp.generated.resources.con2
import laescena.composeapp.generated.resources.IMG_3474
import laescena.composeapp.generated.resources.IMG_4263
import laescena.composeapp.generated.resources.IMG_6064_edited
import laescena.composeapp.generated.resources.IMG_6194
import laescena.composeapp.generated.resources.IMG_6625_edited
import laescena.composeapp.generated.resources.IMG_6633
import laescena.composeapp.generated.resources.IMG_6790
import laescena.composeapp.generated.resources.compose_multiplatform

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

    NavHost(
        navController = navController,
        startDestination = "home"
    ) {
        composable("home") {
            HomeScreen(navController)
        }

        composable("login") {
            LoginScreen(
                onLoginSuccess = { role ->
                    navController.navigate(role) {
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
        composable("superadmin") { SuperAdminScreen() }
        composable("artist") { ArtistScreen() }
        composable("centrocultural") { CentroCulturalScreen() }

        composable("agenda") { AgendaScreen() }


        // Rutas de pantallas
        composable("eventos") { EventosScreen() }
        composable("galerias") { GaleriaScreen() }

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
    ) {

        // Header
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .padding(horizontal = 16.dp, vertical = 12.dp),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically

            Text(
            text = "Bienvenido a La Escena",
            style = MaterialTheme.typography.headlineMedium
        )
        Spacer(modifier = Modifier.height(30.dp))
        Button(onClick = { navController.navigate("eventos") }) { Text("Ver eventos") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Ver artistas") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {
            navController.navigate("galerias")
        }) { Text("Galerías disponibles") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Centro cultural") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(
            onClick = { navController.navigate("register") }

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
                                    Icons.Default.ArrowForward,
                                    contentDescription = null,
                                    tint = Color.White,
                                    modifier = Modifier.size(16.dp)
                                )
                            }
                        }
                    }
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
                                    Icons.Default.ArrowForward,
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

@Preview

@Composable
fun SuperAdminScreen() {
    Column(
        Modifier.fillMaxSize(),
        verticalArrangement = Arrangement.Center,
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        Text(text = "Super Admin")
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Ver eventos") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Ver artistas") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Centro Cultural") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Galerías disponibles") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Crear evento") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Crear artista") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Crear galería") }
    }
}

@Composable
fun ArtistScreen() {
    Column(
        Modifier.fillMaxSize(),
        verticalArrangement = Arrangement.Center,
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        Text(text = "Artista")
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Ver eventos") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Perfil") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Mensajes") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Portafolio Artistico") }
    }
}

@Composable
fun CentroCulturalScreen() {
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
    // En el registro de tu servidor Ktor, solo se piden estos campos + role.
    // He quitado el resto para evitar el error de conversion.

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
            .padding(20.dp)
            .verticalScroll(rememberScrollState()),
        verticalArrangement = Arrangement.Center,
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        Text("Registro de Artista", style = MaterialTheme.typography.headlineMedium)

        Spacer(modifier = Modifier.height(20.dp))

        OutlinedTextField(value = email, onValueChange = { email = it }, label = { Text("Correo electrónico") })
        Spacer(modifier = Modifier.height(10.dp))
        OutlinedTextField(
            value = password,
            onValueChange = { password = it },
            label = { Text("Contraseña") },
            visualTransformation = PasswordVisualTransformation()
        )

        Spacer(modifier = Modifier.height(20.dp))

        Button(
            onClick = {
                // Mandamos "artista" como rol fijo para esta pantalla
                viewModel.registrar(email, password, "artista")
            }
        ) {
            Text("Registrarse")
        }

        Spacer(modifier = Modifier.height(10.dp))

        Text(text = resultMessage, color = if (resultMessage.contains("Error")) Color.Red else Color.Black)
    }
}

@Preview
@Composable
fun EventosScreen() {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .padding(horizontal=38.dp)
            .verticalScroll(rememberScrollState()), // Habilita el scroll vertical
        verticalArrangement = Arrangement.Top, // Alinea al inicio para que el contenido fluya hacia abajo
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        Spacer(modifier = Modifier.height(38.dp)) // Espacio superior
        Text(text = "Eventos en existencia", style = MaterialTheme.typography.headlineLarge)
        Spacer(modifier = Modifier.height(38.dp))


        //Imagenes
        Text(text="Anper Bajo el radar",
            modifier = Modifier.fillMaxWidth(),
            textAlign = TextAlign.Left,
            style = MaterialTheme.typography.headlineMedium)
        Spacer(modifier = Modifier.height(10.dp))
        Image(
            painter = painterResource(Res.drawable.con1),
            contentDescription = "Anper Bajo el radar",
            modifier = Modifier.size(300.dp) // Ajusta el tamaño según necesites
        )
        Spacer(modifier = Modifier.height(8.dp))
        Text(text = "Anper en vivo desde el centro cultural Ricardo Garibay el 25 de " +
                "marzo del 2026, ven y celebra el Punk/Rock 2000ero para cantar, bailar" +
                " y llorar al máximo con esta banda, recibe la primavera como si fuera 2005" +
                " y vinieras de ver la pelea de emos vs Punks con tus converse sucios.",
            modifier = Modifier.fillMaxWidth(),
            textAlign = TextAlign.Left,
            style = MaterialTheme.typography.bodyMedium)

        Spacer(modifier = Modifier.height(38.dp))

        Text(text="¿Quién es ese pxndjx y por qué tiene un concierto?",
            modifier = Modifier.fillMaxWidth(),
            style = MaterialTheme.typography.headlineMedium)
        Spacer(modifier = Modifier.height(10.dp))
        Image(
            painter = painterResource(Res.drawable.con2),
            contentDescription = "El pendejo en vivo",
            modifier = Modifier.size(300.dp) )// Ajusta el tamaño según necesites
        Spacer(modifier = Modifier.height(8.dp))
        Text(text = "¿Alguna vez te has sentido lo suficientemente mal por ver" +
                " como alguien a quien nadie conoce esta logrando los sueños que" +
                " tu tenias de pequeño? Gerardo Bracho también, por eso mismo este artista" +
                " emergente de la ciudad de Pachuca Hidalgo trae a nosotros su proyecto como" +
                " solista prometiendo una noche Bohemia el dia 23 de abril del 2026.",
            modifier = Modifier.fillMaxWidth(),
            textAlign = TextAlign.Left,
            style = MaterialTheme.typography.bodyMedium)

        Spacer(modifier = Modifier.height(38.dp)) // Espacio final
    }
}

@Preview
@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun GaleriaScreen() {
    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Galerias disponibles") }

            )
        }) { paddingValues ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(paddingValues)
                .padding(horizontal = 38.dp)
                .verticalScroll(rememberScrollState()),
            horizontalAlignment = Alignment.CenterHorizontally
        ) {
            Spacer(modifier = Modifier.height(20.dp))

            //Titulo de la galeria
            Text(
                text = "Souvenir",
                style = MaterialTheme.typography.headlineMedium
            )
            Spacer(modifier = Modifier.height(16.dp))


            //Imagenes

            @Composable
            fun GaleriaPic(titulo: String, imagen: Painter){
                Column {
                    Text(
                        text = titulo,
                        style = MaterialTheme.typography.headlineSmall,
                        fontWeight = FontWeight.Bold
                    )

                    Spacer(modifier = Modifier.height(8.dp))

                    Image(
                        painter = imagen,
                        contentDescription = titulo,
                        modifier = Modifier.fillMaxWidth()
                    )
                }
            }

            GaleriaPic("Recuerdo borroso", painterResource(Res.drawable.IMG_3474))
            Spacer(modifier = Modifier.height(8.dp))
            GaleriaPic("Me entiende más un caballo", painterResource(Res.drawable.IMG_4263))
            Spacer(modifier = Modifier.height(8.dp))
            GaleriaPic("m3m0r14z", painterResource(Res.drawable.IMG_6064_edited))
            Spacer(modifier = Modifier.height(38.dp))
            GaleriaPic("tres viajes más y se  acaba", painterResource(Res.drawable.IMG_6194))
            Spacer(modifier = Modifier.height(8.dp))
            GaleriaPic("¿Con todo?", painterResource(Res.drawable.IMG_6625_edited))
            Spacer(modifier = Modifier.height(8.dp))
            GaleriaPic("¿Con chile del que pica?", painterResource(Res.drawable.IMG_6633))
            Spacer(modifier = Modifier.height(8.dp))
            GaleriaPic("50 X persona", painterResource(Res.drawable.IMG_6790))
            Spacer(modifier = Modifier.height(8.dp))

        }
    }
}