#include <iostream>
#include<sstream>
#include <string.h>
#include <unistd.h>
#include <ctype.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <arpa/inet.h>
#include <errno.h>
#include <stdlib.h>
#include <signal.h>
#include <sys/epoll.h>
#include <fcntl.h>
#include <pthread.h>
#include <sys/time.h>
#include <sys/resource.h>
#include <errno.h>
#include "global.h"
#include "log.h"
#include "tinyxml.h"

using namespace std;

FdQueue g_fdqueue;
map<string,TTCircle*> g_ttc;
string g_cfg;

void setnonblocking(int sock);
void daemon();
int setmaxfiledesc();
int lock_wait(const char * fname);
void partner(const char * lockname, char* argv[]);

int main(int argc,char *argv[])
{
	if( argc < 2 )
	{
		cout<<" input : "<<argv[0]<<" cfg-file"<<endl;
		exit(0);
	}

	setmaxfiledesc();
	g_cfg = argv[1];
	TiXmlDocument *myDocument = new TiXmlDocument(g_cfg.c_str());
  myDocument->LoadFile();
  TiXmlElement *RootElement = myDocument->RootElement();
  TiXmlElement *partElement = RootElement->FirstChildElement("partner");
  TiXmlElement *childElement = partElement->FirstChildElement("file");
  string partfile = childElement->GetText();
	daemon();
  partner(partfile.c_str(), argv);
	TiXmlElement *logElement  =  RootElement->FirstChildElement("log");
  childElement = logElement->FirstChildElement("file");
  string file = childElement->GetText();
  childElement = logElement->FirstChildElement("rank");
  string rank = childElement->GetText();
  LOG_INIT(file.c_str(), rank );
  LOG_OPEN();
  LOG_INFO("init log finish [log file:] %s [rank:]%s",file.c_str(),rank.c_str());
	
	TiXmlElement *ttcElement  =  RootElement->FirstChildElement("ttc");
	childElement = ttcElement->FirstChildElement("table");
  string table = childElement->GetText();
  TTCircle* pttc = new TTCircle;
	TiXmlElement *ttElement  =  ttcElement->FirstChildElement("tt");
	childElement = ttElement->FirstChildElement("ip");
	string ip = childElement->GetText();
	childElement = ttElement->FirstChildElement("port");
	string port = childElement->GetText();
	childElement = ttElement->FirstChildElement("name");
	string name = childElement->GetText();
	pttc->add_server(name,ip,atoi(port.c_str()));
	LOG_INFO(" %s %d %s table [%s]  .",__FILE__,__LINE__,__FUNCTION__,table.c_str());
	while(true)
  {
  	ttElement  =  ttElement->NextSiblingElement("tt");
    if(NULL == ttElement ) break;
    childElement = ttElement->FirstChildElement("ip");
		ip = childElement->GetText();
		childElement = ttElement->FirstChildElement("port");
		port = childElement->GetText();
		childElement = ttElement->FirstChildElement("name");
		name = childElement->GetText();
		pttc->add_server(name,ip,atoi(port.c_str()));
	}
	pttc->finish_consist();		
	g_ttc.insert(make_pair<string,TTCircle*>(table,pttc));
	LOG_INFO(" %s %d %s table [%s]  .",__FILE__,__LINE__,__FUNCTION__,table.c_str());
	
	while(true)
	{
		ttcElement  =  ttcElement->NextSiblingElement("ttc");
		if(NULL == ttcElement ) break;
		childElement = ttcElement->FirstChildElement("table");
	  table = childElement->GetText();
	  pttc = new TTCircle;
		ttElement  =  ttcElement->FirstChildElement("tt");
		childElement = ttElement->FirstChildElement("ip");
		ip = childElement->GetText();
		childElement = ttElement->FirstChildElement("port");
		port = childElement->GetText();
		childElement = ttElement->FirstChildElement("name");
		name = childElement->GetText();
		pttc->add_server(name,ip,atoi(port.c_str()));
		LOG_INFO(" %s %d %s table [%s]  .",__FILE__,__LINE__,__FUNCTION__,table.c_str());
		while(true)
	  {
	  	ttElement  =  ttElement->NextSiblingElement("tt");
	    if(NULL == ttElement ) break;
	    childElement = ttElement->FirstChildElement("ip");
			ip = childElement->GetText();
			childElement = ttElement->FirstChildElement("port");
			port = childElement->GetText();
			childElement = ttElement->FirstChildElement("name");
			name = childElement->GetText();
			pttc->add_server(name,ip,atoi(port.c_str()));
		}
		pttc->finish_consist();		
		g_ttc.insert(make_pair<string,TTCircle*>(table,pttc));
		LOG_INFO(" %s %d %s table [%s]  .",__FILE__,__LINE__,__FUNCTION__,table.c_str());
		
	}
	
	TiXmlElement *proxyElement  =  RootElement->FirstChildElement("proxy");
	childElement = proxyElement->FirstChildElement("ttconnum");
  string tcnum = childElement->GetText();
	TTCircle::TT_CONNECT_NUM = atoi(tcnum.c_str());
	childElement = proxyElement->FirstChildElement("threadnum");
  string threadnum = childElement->GetText();
	LOG_INFO("init proxy finish [ttconnum:] %s [threadnum:]%s",tcnum.c_str(),threadnum.c_str());

	TiXmlElement *tcpElement  =  RootElement->FirstChildElement("tcp");
	childElement = tcpElement->FirstChildElement("ip");
  ip = childElement->GetText();
	TTCircle::TT_CONNECT_NUM = atoi(tcnum.c_str());
	childElement = tcpElement->FirstChildElement("port");
  port = childElement->GetText();
	LOG_INFO("init tcp finish [ip:] %s [port:]%s",ip.c_str(),port.c_str());
	delete myDocument;
	
	struct sockaddr_in sin;
	bzero(&sin, sizeof(sin));
  sin.sin_family = AF_INET;
  sin.sin_addr.s_addr = inet_addr(ip.c_str());
  sin.sin_port = htons(atoi(port.c_str()));

  int listen_fd = socket(AF_INET, SOCK_STREAM, 0);
  if (-1 == listen_fd)
  {
  	  LOG_ERROR(" %s %d %s socket create error %d %s.",__FILE__,__LINE__,__FUNCTION__,errno ,strerror(errno));
      exit(1);
  }
  int optint = 1;
  if(setsockopt(listen_fd, SOL_SOCKET, SO_REUSEADDR, (char *)&optint, sizeof(optint)) != 0){
     LOG_ERROR(" %s %d %s setsockopt error %d %s.",__FILE__,__LINE__,__FUNCTION__,errno ,strerror(errno));
      exit(1);
  }
  int ret = bind(listen_fd, (struct sockaddr *)&sin, sizeof(sin));
  if (-1 == ret )
  {
  	LOG_ERROR(" %s %d %s socket bind error %d %s.",__FILE__,__LINE__,__FUNCTION__,errno ,strerror(errno));
    exit(1);
  }
  ret = listen(listen_fd, 1024);
  if (-1 == ret )
  {
  	  LOG_ERROR(" %s %d %s socket listen error %d %s.",__FILE__,__LINE__,__FUNCTION__,errno ,strerror(errno));
      exit(1);
  }
  
  pthread_t tid;
  int thnum = atoi(threadnum.c_str());
  for( int i=0 ; i<thnum; i++)
  {
	  if (pthread_create(&tid, NULL, work_thread, NULL) == -1) {
	  	LOG_ERROR(" %s %d %s pthread_create error %d %s.",__FILE__,__LINE__,__FUNCTION__,errno ,strerror(errno));
			return -1;
		}
  }

  if (pthread_create(&tid, NULL, udp_thread, NULL) == -1) {
  	LOG_ERROR(" %s %d %s pthread_create error %d %s.",__FILE__,__LINE__,__FUNCTION__,errno ,strerror(errno));
		return -1;
	}
  struct epoll_event ev;
  struct epoll_event * events = (struct epoll_event *)malloc(sizeof(struct epoll_event)*8192);
  int epfd = epoll_create(8192);
  if( -1 == epfd )
  {
  	LOG_ERROR(" %s %d %s epoll_create error %d %s.",__FILE__,__LINE__,__FUNCTION__,errno ,strerror(errno));
  	exit(1);
  }
  setnonblocking(listen_fd);
  ev.events = EPOLLIN | EPOLLET;
  ev.data.fd = listen_fd;
  if (epoll_ctl(epfd, EPOLL_CTL_ADD, listen_fd, &ev) < 0) 
  {
  	LOG_ERROR(" %s %d %s epoll_ctl ADD error %d %s.",__FILE__,__LINE__,__FUNCTION__,errno ,strerror(errno));
    return -1;
  }
  struct sockaddr_in caddr;
  int addrlen = sizeof(struct sockaddr_in);
  for(;;)
  {
  	int nfds = epoll_wait(epfd, events, 8192, 1);
    if ( -1 == nfds )  
    {
    	if( EINTR == errno ) continue;
    	LOG_ERROR(" %s %d %s epoll_wait error %d %s.",__FILE__,__LINE__,__FUNCTION__,errno ,strerror(errno));
      break;  
    }
    if( 0 == nfds )
    {
    	
    }  
    for(int n = 0; n < nfds; ++n)
    {
    	int client = accept(listen_fd, (struct sockaddr *)(&caddr), (socklen_t *)(&addrlen));
    	if( -1 == client )
    	{
    		LOG_ERROR(" %s %d %s accept error %d %s.",__FILE__,__LINE__,__FUNCTION__,errno ,strerror(errno));
    		break;
    	}
    	g_fdqueue.Push(client);
    	LOG_INFO(" %s %d %s connect from %s\n ",__FILE__,__LINE__,__FUNCTION__,inet_ntoa(caddr.sin_addr));
    }
  	
  }
  LOG_CLOSE();
  return 0;
}

void setnonblocking(int sock)
{
  int opts=fcntl(sock,F_GETFL);
	if(opts<0)
  {
  	LOG_ERROR(" %s %d %s fcntl get error %d %s.",__FILE__,__LINE__,__FUNCTION__,errno ,strerror(errno));
    exit(1);
  }
  opts = opts|O_NONBLOCK;
  if(fcntl(sock,F_SETFL,opts)<0)
  {
  	LOG_ERROR(" %s %d %s fcntl set error %d %s.",__FILE__,__LINE__,__FUNCTION__,errno ,strerror(errno));
    exit(1);
  }
}

void daemon()
{
    pid_t iPID;
    
    if((iPID = fork()) < 0)
    {
        exit(0);
    }
    else if(iPID > 0){
        exit(0);
	  }

    setsid();

    signal(SIGINT,  SIG_IGN);
    signal(SIGHUP,  SIG_IGN);
    signal(SIGQUIT, SIG_IGN);
    signal(SIGPIPE, SIG_IGN);
    signal(SIGTTOU, SIG_IGN);
    signal(SIGTTIN, SIG_IGN);
    
    signal(SIGTERM, SIG_IGN);

    if((iPID = fork()) < 0) 
    {
        exit(0);
    }
    else if(iPID > 0){ 
        exit(0);
    }

    chdir("/");
    
    for (int fd=0,fdtablesize=getdtablesize();fd < fdtablesize;fd++)
    {
    		close(fd);
    }
    
    umask(0);
    signal(SIGCHLD, SIG_IGN); 
    
}

int setmaxfiledesc()
{
	struct rlimit rlim;
	rlim.rlim_cur = 20480;
	rlim.rlim_max = 20480;
	if (setrlimit(RLIMIT_NOFILE, &rlim) != 0)
	{
	  LOG_ERROR(" %s %d %s setrlimit error %d %s.",__FILE__,__LINE__,__FUNCTION__,errno ,strerror(errno));
	}
	else
	{
	  LOG_INFO(" %s %d %s fcntl set error %d %s.",__FILE__,__LINE__,__FUNCTION__,errno ,strerror(errno));
	}
	return 0;
}

void partner(const char * lockname, char* argv[])
{
    char * arg[] = {argv[0], argv[1], (char *)0};
    int ret = lock_wait(lockname);
    if(0 == ret ){
            //printf("lock ok\n");
      int pid = fork();
      if(0==pid){
            //printf("[child] %s %s\n",arg[0],arg[1]);
            execv(arg[0], arg);
      }
      sleep(1);
    }
}

int lock_wait(const char * fname)
{
    //cout<<"--lock----[fname:]"<<fname<<endl;
    int fd = open(fname, O_RDWR | O_CREAT, 0666);

    if( fd < 0 ){
            //perror("open");
            return -1;
    }

    struct flock lock;
    lock.l_whence = SEEK_SET;
    lock.l_start = 0;
    lock.l_len = 0;
    lock.l_type = F_WRLCK;

  int error = -1;
  
  do{
  
    error = fcntl(fd, F_SETLKW, &lock);  
  
  }while(-1==error && EINTR == errno);
  
    return 0;

}

