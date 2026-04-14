plugins {
    alias(libs.plugins.kotlin.jvm)
    alias(libs.plugins.ktor)
    // Usamos el mismo motor de versión que el plugin de Kotlin para evitar conflictos
    id("org.jetbrains.kotlin.plugin.serialization") version "2.0.21"
}

group = "KtorLaEscena"
version = "0.0.1"
repositories {
    mavenCentral()
}

application {
    mainClass = "io.ktor.server.netty.EngineMain"
}
// ← AGREGAR ESTO
ktor {
    fatJar {
        archiveFileName.set("app.jar")
    }
}
kotlin {
    jvmToolchain(21)
}

dependencies {
    // Ktor Server Core y Extensiones (Usando versiones del catálogo libs)
    implementation(libs.ktor.server.core)
    implementation(libs.ktor.server.netty)
    implementation(libs.ktor.server.content.negotiation)
    implementation(libs.ktor.server.default.headers)
    implementation(libs.ktor.server.call.logging)
    
    // Serialización JSON (Necesaria para comunicarse con la App móvil)
    implementation("io.ktor:ktor-serialization-kotlinx-json-jvm:3.0.0")

    // Logs para ver qué pasa en el servidor
    implementation(libs.logback.classic)

    // Base de Datos (MySQL y Exposed)
    implementation("com.mysql:mysql-connector-j:8.3.0")
    implementation("org.jetbrains.exposed:exposed-core:0.50.1")
    implementation("org.jetbrains.exposed:exposed-dao:0.50.1")
    implementation("org.jetbrains.exposed:exposed-jdbc:0.50.1")

    // Tests
    testImplementation(libs.ktor.server.test.host)
    testImplementation(libs.kotlin.test.junit)
}
