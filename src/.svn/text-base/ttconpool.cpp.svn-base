#include "ttconpool.h"
#include <sys/socket.h>
#include <netinet/in.h>
#include <arpa/inet.h>
#include "log.h"
#include <errno.h>
#include <sys/types.h>
#include <netdb.h>
#include <netinet/tcp.h>
#include <string.h>
void set_keepalive(int fd, int keep_alive, int keep_idle, int keep_interval, int keep_count)
{
        if(keep_alive)
        {
                if(setsockopt(fd, SOL_SOCKET, SO_KEEPALIVE, 
                                        (void*)&keep_alive, sizeof(keep_alive)) == -1)
                {
                        LOG_ERROR(" %s %d %s setsockopt SOL_SOCKET::SO_KEEPALIVE failed, %s\n",__FILE__,__LINE__,__FUNCTION__,strerror(errno));
                }
                if(setsockopt(fd, SOL_TCP, TCP_KEEPIDLE,
                                        (void *)&keep_idle,sizeof(keep_idle)) == -1)
                {
                        LOG_ERROR(" %s %d %s setsockopt SOL_TCP::TCP_KEEPIDLE failed, %s\n",__FILE__,__LINE__,__FUNCTION__, strerror(errno));
                }
                if(setsockopt(fd,SOL_TCP,TCP_KEEPINTVL,
                                        (void *)&keep_interval, sizeof(keep_interval)) == -1)
                {
                        LOG_ERROR(" %s %d %s setsockopt SOL_tcp::TCP_KEEPINTVL failed, %s\n",__FILE__,__LINE__,__FUNCTION__, strerror(errno));
                }
                if(setsockopt(fd,SOL_TCP,TCP_KEEPCNT,
                                        (void *)&keep_count,sizeof(keep_count)) == -1)
                {
                        LOG_ERROR(" %s %d %s setsockopt SOL_TCP::TCP_KEEPCNT failed, %s\n",__FILE__,__LINE__,__FUNCTION__, strerror(errno));
                }
        }
}

int netcheck(int fd)
{
        int buf_size = 1024;
        char buf[buf_size];
				int n = 0;
        //clear OOB DATA 
        n = recv(fd, buf, buf_size, MSG_OOB|MSG_NOSIGNAL);
        n = send(fd, (void *)"0", 1, MSG_OOB|MSG_NOSIGNAL);
        n = send(fd, (void *)"0", 1, MSG_OOB|MSG_NOSIGNAL);
        //LOG_INFO(" %s %d %s send oob %d ",__FILE__,__LINE__,__FUNCTION__,n);
        if( n < 0 )
        {
                LOG_ERROR(" %s %d %s Connection[%d] send OOB failed, %s\n",__FILE__,__LINE__,__FUNCTION__, fd, strerror(errno));
                return -1;
        }
        return 0;
}

TTconPool:: TTconPool()
{
  	memset(m_host,0,sizeof(m_host));
  	m_realsize = 0;
}

TTconPool:: ~TTconPool()
{

}

int TTconPool:: Init(int size,string host,int port)
{
  	strncpy(m_host,host.c_str(),sizeof(m_host));
  	m_port = port;
  	m_size = size;
  	m_deque.clear();
  	m_realsize = 0;

  	for(int i = 0; i < size; i++)
  	{
  		TTCON* conn = CreateConnection();
  		
  		m_realsize++;
  		m_deque.push_back(conn);
  		if ( -1 == conn->sock )
  		{
  			return 0;
  		}
  	}

  	return 1;
}

void TTconPool:: UnInit()
{
	  Thread_Mutex_Guard v2(&m_CriticalSection);

  	deque<TTCON*>::const_iterator it;
  	for(it = m_deque.begin();it != m_deque.end();it++)
  	{
  		close((*it)->sock);
  		delete *it;
  	}
  	m_deque.clear();
}

void TTconPool:: ReConnectAll()
{
	m_CriticalSection.acquire();
  for(deque<TTCON*>::iterator it=m_deque.begin();it!=m_deque.end();it++)
  {
		if( 0 != netcheck((*it)->sock) )
		{
			Reconnect(*it);		
		}
	}
	m_CriticalSection.release();
}

TTCON* TTconPool:: GetConnection()
{
	  m_CriticalSection.acquire();

  	if (m_deque.size() > 0)
  	{
  		TTCON* cnn = m_deque.front();
  		m_deque.pop_front();
  		
			if( NOCON == cnn->status )
			{
					LOG_INFO(" %s %d %s netcheck failed .",__FILE__,__LINE__,__FUNCTION__);
					Reconnect(cnn);
					for(deque<TTCON*>::iterator it=m_deque.begin();it!=m_deque.end();it++)
					{
						if( 0 != netcheck((*it)->sock) )
						{
							Reconnect(*it);		
						}
					}
			}
			
  		m_CriticalSection.release();	
  		return cnn;
  	}
  	else 
  	{
  		if (m_realsize < m_size)
  		{
  			m_realsize++;
  			m_CriticalSection.release();
  			return CreateConnection();
  		}
  	}

  	m_CriticalSection.release();
  	return NULL;
}

void TTconPool:: ReleaseConnection(TTCON* conn)
{
	Thread_Mutex_Guard v2(&m_CriticalSection);
  m_deque.push_back(conn);
}

void TTconPool:: TerminateConnection(TTCON* conn)
{
  
}

TTCON* TTconPool:: CreateConnection()
{
	 struct sockaddr_in  addr;
   int s  = socket(AF_INET, SOCK_STREAM, 0);
   if( s < 0 )
   {
   	 LOG_ERROR(" %s %d %s socket error %d %s.",__FILE__,__LINE__,__FUNCTION__,errno ,strerror(errno));
   	 return NULL;
   }
   
  struct timeval opttv;
  opttv.tv_sec = 0;
  opttv.tv_usec = (__suseconds_t)(0.95 * 1000000);
  setsockopt(s, SOL_SOCKET, SO_RCVTIMEO, (char *)&opttv, sizeof(opttv));
  opttv.tv_sec = 0;
  opttv.tv_usec = (__suseconds_t)(0.95 * 1000000);
  setsockopt(s, SOL_SOCKET, SO_SNDTIMEO, (char *)&opttv, sizeof(opttv));
  int optint = 1;
  setsockopt(s, IPPROTO_TCP, TCP_NODELAY, (char *)&optint, sizeof(optint));
   
   bzero(&addr, sizeof(addr));
   addr.sin_family = AF_INET;
   addr.sin_port = htons(m_port);
   addr.sin_addr.s_addr= inet_addr(m_host);
   int ret = connect(s, (const sockaddr *)(&addr), sizeof(addr));
   TTCON* cnn = new TTCON;
   if( ret < 0 )
   {
   	 LOG_ERROR(" %s %d %s connect error %d %s.",__FILE__,__LINE__,__FUNCTION__,errno ,strerror(errno));
   	 cnn->sock = -1;
   	 cnn->status = NOCON;
   	 return cnn;
   }
   //set_keepalive(s, 1, 1, 1, 3);
   cnn->sock = s;
   cnn->status = OKCON;
   return cnn;
}

int TTconPool:: Reconnect(TTCON *conn)
{
	 struct sockaddr_in  addr;
   int s  = socket(AF_INET, SOCK_STREAM, 0);
   if( s < 0 )
   {
   	 LOG_ERROR(" %s %d %s socket error %d %s.",__FILE__,__LINE__,__FUNCTION__,errno ,strerror(errno));
   	 return -1;
   }
   struct timeval opttv;
	 opttv.tv_sec = 0;
	 opttv.tv_usec = (__suseconds_t)(0.25 * 1000000);
	 setsockopt(s, SOL_SOCKET, SO_RCVTIMEO, (char *)&opttv, sizeof(opttv));
	 opttv.tv_sec = 0;
	 opttv.tv_usec = (__suseconds_t)(0.25 * 1000000);
	 setsockopt(s, SOL_SOCKET, SO_SNDTIMEO, (char *)&opttv, sizeof(opttv));
	 int optint = 1;
	 setsockopt(s, IPPROTO_TCP, TCP_NODELAY, (char *)&optint, sizeof(optint));
   bzero(&addr, sizeof(addr));
   addr.sin_family = AF_INET;
   addr.sin_port = htons(m_port);
   addr.sin_addr.s_addr= inet_addr(m_host);
   int ret = connect(s, (const sockaddr *)(&addr), sizeof(addr));
   if( ret < 0 )
   {
   	 LOG_ERROR(" %s %d %s connect error %d %s. host %s port %d",__FILE__,__LINE__,__FUNCTION__,errno ,strerror(errno),m_host,m_port);
   	 return -1;
   }
   //set_keepalive(s, 1, 1, 1, 3);
   close(conn->sock);
   conn->sock = s;
   conn->status = OKCON;
   return 0;
}
