using Microsoft.AspNetCore.Mvc;
using System.Text.Json;

namespace CreditCardEnrollment.Controllers;

[ApiController]
public class HealthController(ILogger<HealthController> logger) : ControllerBase
{
    [HttpHead("docker_healthcheck")] // The healthcheck only does HEAD requests
    [HttpGet("docker_healthcheck")] // We want to be able to get a response sometimes to test.
    public IActionResult HealthCheck()
    {
        logger.LogInformation("Health check requested at: {Time}", DateTime.UtcNow);
        
        var healthStatus = new
        {
            Status = "Healthy",
            Timestamp = DateTime.UtcNow,
            DatabaseMode = Environment.GetEnvironmentVariable("INITIALIZE_DATABASES")?.ToLower() != "false" 
                ? "Full" 
                : "HealthCheckOnly",
            Environment = Environment.GetEnvironmentVariable("ASPNETCORE_ENVIRONMENT") ?? "Production"
        };

        logger.LogDebug("Health check details: {Details}", 
            JsonSerializer.Serialize(healthStatus));

        return Ok(healthStatus);
    }
}
