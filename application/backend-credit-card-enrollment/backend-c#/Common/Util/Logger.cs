using Microsoft.Extensions.Logging.Abstractions;
using Microsoft.Extensions.Logging.Console;

namespace CreditCardEnrollment.Common.Util;


public class Logger : ConsoleFormatter
{
    public Logger() : base("MainLogger") { }

    public override void Write<TState>(
        in LogEntry<TState> logEntry,
        IExternalScopeProvider? scopeProvider,
        TextWriter textWriter)
    {
        var message = logEntry.Formatter?.Invoke(logEntry.State, logEntry.Exception);
        if (message == null) {
            return;
        }

        var threadId = Thread.CurrentThread.ManagedThreadId;
        var category = logEntry.Category ?? "Unknown";  
        var timestamp = DateTimeOffset.Now.ToString("[yyyy-MM-dd HH:mm:ss.fff] ");
        textWriter.WriteLine($"{timestamp} [{threadId}] [{category}] [{logEntry.LogLevel}] {message}");
        
        if (logEntry.Exception != null) {
            textWriter.WriteLine($"Exception: {logEntry.Exception}");
        }
    }
}