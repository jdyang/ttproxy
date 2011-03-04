// log.cpp

#include "log.h"

#include <stdlib.h>
#include <stdarg.h>
#include <string.h>
#include <ctype.h>
#include <sys/stat.h>
#include <unistd.h>

#define DATE_START  7
    char Log::level_str_[][64] = {
        "\033[1;31m2008-11-07 09:35:00 FATAL ", 
        "\033[1;33m2008-11-07 09:35:00 ERROR ", 
        "\033[1;35m2008-11-07 09:35:00 WARN  ", 
        "\033[1;32m2008-11-07 09:35:00 INFO  ", 
        "\033[0;00m2008-11-07 09:35:00 TRACE ", 
        "\033[0;00m2008-11-07 09:35:00 DEBUG "
    };

#define TIME_START  (DATE_START + 11)

    Log::Log()
    {
        memset(file_name_, 0, sizeof(file_name_));
        max_size_ = 1792 * 1024 * 1024;  // 1.8G
        lock_ = new Thread_Mutex;
        file_ = NULL;
        max_level_ = L_INFO;
    }

    Log::~Log()
    {
        close();
        delete lock_;
        lock_ = NULL;
    }

    int Log::set_file_name(const char *filename)
    {
        // 已经打开，不能设置了
        if (file_ != NULL) {
            return -1;
        }

        strcpy(file_name_, filename);
        return 0;
    }

    int Log::set_max_size(size_t maxsize)
    {
        max_size_ = maxsize;
        // 不立即生效
        return 0;
    }

    void Log::set_max_level(string level)
    {
        LogLevel log_level = L_INFO;
        if( "DEBUG" == level)
        {
            log_level = L_DEBUG;
        }
        else if( "TRACE" == level)
        {
            log_level = L_TRACE;
        }
        else if( "INFO" == level)
        {
            log_level = L_INFO;
        }
        else if( "WARN" == level)
        {
            log_level = L_WARN;
        }
        else if( "ERROR" == level)
        {
            log_level = L_ERROR;
        }
        else if( "FATAL" == level)
        {
            log_level = L_FATAL;
        }
        max_level_ = log_level;
    }

    int Log::open()
    {
        if (file_ != NULL) {
            return -1;
        }


        char name[MAX_PATH];
        size_t len = 0;

        strncpy(name, file_name_, MAX_PATH);
        len = strlen(name);

        time_t t;
        time(&t);
        struct tm lt = *localtime(&t);
        strftime(name + len, MAX_PATH - len, "-%Y%m%d-%H%M%S.log", &lt);

        file_ = fopen(name, "a+");
        if (NULL == file_)
            return -1;

        // 填写日志记录中的日期，在一天之内就不用填写了
        strftime(name, 12, "%Y-%m-%d", &lt);
        for (int i = 0; i < L_LEVEL_MAX; i++) {
            memcpy(level_str_[i] + DATE_START, name, 10);
        }

        lt.tm_hour = lt.tm_min = lt.tm_sec = 0;
        mid_night_ = mktime(&lt);
        
        return 0;
    }

    int Log::close()
    {
        if (NULL == file_) {
            return -1;
        }

        fclose(file_);
        file_ = NULL;

        return 0;
    }

    int Log::log(LogLevel level, const char* fmt, ...)
    {
        va_list ap;
        va_start(ap, fmt);
        int ret = vlog(level, fmt, ap); // not safe
        va_end(ap);
        return ret;
    }

    int Log::log_fatal(const char* fmt, ...)
    {
        va_list ap;
        va_start(ap, fmt);
        int ret = vlog(L_FATAL, fmt, ap);
        va_end(ap);
        return ret;
    }

    int Log::log_error(const char* fmt, ...)
    {
        va_list ap;
        va_start(ap, fmt);
        int ret = vlog(L_ERROR, fmt, ap);
        va_end(ap);
        return ret;
    }

    int Log::log_warn(const char* fmt, ...)
    {
        va_list ap;
        va_start(ap, fmt);
        int ret = vlog(L_WARN, fmt, ap);
        va_end(ap);
        return ret;
    }

    int Log::log_info(const char* fmt, ...)
    {
        va_list ap;
        va_start(ap, fmt);
        int ret = vlog(L_INFO, fmt, ap);
        va_end(ap);
        return ret;
    }

    int Log::log_trace(const char* fmt, ...)
    {
        va_list ap;
        va_start(ap, fmt);
        int ret = vlog(L_TRACE, fmt, ap);
        va_end(ap);
        return ret;
    }

    int Log::log_debug(const char* fmt, ...)
    {
        va_list ap;
        va_start(ap, fmt);
        int ret = vlog(L_DEBUG, fmt, ap);
        va_end(ap);
        return ret;
    }

    int Log::vlog(int level, const char * fmt, va_list ap)
    {
        if (level > max_level_ || NULL == file_)
            return -1;
        Thread_Mutex_Guard mutexGuard(lock_);

        int t_diff = (int)(time(NULL) - mid_night_);
        if (t_diff > 24 * 60 * 60) {
            time_t t;
            time(&t);
            struct tm lt = *localtime(&t);
            // 填写日志记录中的日期，在一天之内就不用填写了
            char name[32];
            strftime(name, 12, "%Y-%m-%d", &lt);
            for (int i = 0; i < L_LEVEL_MAX; i++) {
                memcpy(level_str_[i] + DATE_START, name, 10);
            }
            lt.tm_hour = lt.tm_min = lt.tm_sec = 0;
            mid_night_ = mktime(&lt);
            t_diff -= 24 * 60 * 60;
        }

        register int hour = t_diff / 3600;
        register int minute = t_diff % 3600;
        register int second = minute % 60;
        minute /= 60;

        level_str_[level][TIME_START] = hour / 10 + '0';
        level_str_[level][TIME_START + 1] = hour % 10 + '0';
        level_str_[level][TIME_START + 3] = minute / 10 + '0';
        level_str_[level][TIME_START + 4] = minute % 10 + '0';
        level_str_[level][TIME_START + 6] = second / 10 + '0';
        level_str_[level][TIME_START + 7] = second % 10 + '0';


        fputs(level_str_[level], file_);
        // write msg
        vfprintf(file_, fmt, ap);
 
        if (fmt[strlen(fmt) - 1] != '\n')
            fputc('\n', file_);

        if ((size_t)ftell(file_) > max_size_) {
            close();
            open();
        }
        return 0;
    }

    static const char chex[] = "0123456789ABCDEF";

    int Log::log_hex(
        LogLevel level,
        unsigned char * data, 
        size_t len)
    {
        size_t i, j, k, l;

        if (level > max_level_)
            return -1;

        char msg_str[128];

        msg_str[0] = '[';
        msg_str[3] = '0';
        msg_str[4] = ']';
        msg_str[57] = ' ';
        msg_str[58] = '|';
        msg_str[75] = '|';
        msg_str[76] = 0;
        k = 4;
        for (j = 0; j < 16; j++)
        {
            if ((j & 0x03) == 0)
            {
                msg_str[++k] = ' ';
            }
            k += 3;
            msg_str[k] = ' ';
        }

		Thread_Mutex_Guard mutexGuard(lock_);
        for (i = 0; i < len / 16; i++)
        {
        		while( i > 255 ) i = i % 256;
            msg_str[1] = chex[i >> 4];
            msg_str[2] = chex[i &0xF];
            k = 5;
            l = i * 16;
            memcpy(msg_str + 59, data + l, 16);
            for (j = 0; j < 16; j++)
            {
                if ((j & 0x03) == 0)
                {
                    k++;
                }
                msg_str[k++] = chex[data[l] >> 4];
                msg_str[k++] = chex[data[l++] & 0x0F];
                k++;
                if (!isgraph(msg_str[59 + j]))
                    msg_str[59 + j]= '.';
            }
            fprintf(file_, "# %s\n", msg_str);
        }
        while( i > 255 ) i = i % 256;
        msg_str[1] = chex[i >> 4];
        msg_str[2] = chex[i &0xF];
        k = 5;
        l = i * 16;
        memcpy(msg_str + 59, data + l, len % 16);
        for (j = 0; j < len % 16; j++)
        {
            if ((j & 0x03) == 0)
            {
                k++;
            }
            msg_str[k++] = chex[data[l] >> 4];
            msg_str[k++] = chex[data[l++] & 0x0F];
            k++;
            if (!isgraph(msg_str[59 + j]))
                msg_str[59 + j]= '.';
        }
        for (; j < 16; j++)
        {
            if ((j & 0x03) == 0)
            {
                k++;
            }
            msg_str[k++] = ' ';
            msg_str[k++] = ' ';
            k++;
            msg_str[59 + j]= ' ';
        }
        fprintf(file_, "# %s\n", msg_str);

        return 0;
    }
 
    Log Log::global_log;
